<?php
/**
 * DomusSphinxClient
 *
 * based on sfSphinxClient class of Sphinx project
 *
 * @package    symfony
 * @subpackage routing
 * @author     Cherednikov Alexey
 * @version    SVN: $Id$
 */
class DomusSphinxClient extends sfSphinxClient
{
  public
    $_options = array(
      'path'          => false,
      'socket'        => false,
      'offset'        => 0,
      'limit'         => 10,
      'mode'          => self::SPH_MATCH_ANY,
      'weights'       => array(),
      'min_id'        => 0,
      'max_id'        => 0xFFFFFFFF,
      'filters'       => array(),
      'maxmatches'    => 10000,
      'anchor'        => array(),
      'indexweights'  => array(),
      'maxquerytime'  => 0,
      'fieldweights'  => array(),
      'overrides'     => array(),
      'select'        => '*',
      'mbenc'         => '',
      'arrayresult'   => true,
      'timeout'       => 0,
    ),
    $_search_query    = '';

  public function __construct($options)
  {
    $this->_options = array_merge(
      sfConfig::get('app_sphinx', array()),
      $this->_options
    );

    $this->_options = array_merge($this->_options, $options);

    parent::__construct($this->_options);
  }
 
  public function getRegionLotsCount($params = array())
  {
    $this->ResetFilters();
    $this->ResetGroupBy();
    $this->ResetOverrides();
    $this->_search_query = '';
    
    $index_type = str_replace('-', '_', $params['type']);

    $this->setFilter('type', array(Lot::$types[Lot::getRealType($params['type'])]));
    $this->setFilter('status', array(Lot::$statuses['active']));
    $this->setFilter('region_id', array($params['region_id']));
    $this->setFilterRange('created_at_ts', 0, time());
    $this->setFilterRange('active_till_ts', 0, strtotime('-1 hour'), true);
    $this->setMatchMode(self::SPH_MATCH_EXTENDED);
    $this->SetLimits(0,1,1);
    return $this->query('', $index_type . '_main ' . $index_type . '_delta');
  }

  public function search($params = array())
  {
    $this->ResetFilters();
    $this->ResetGroupBy();
    $this->ResetOverrides();
    $this->_search_query = '';

    $this->_params = $params;
    $index_type = str_replace('-', '_', $this->_params['type']);

    $this->setFilterRange('created_at_ts', 0, time());
    $this->setFilterRange('active_till_ts', 0, strtotime('-1 hour'), true);
    $this->setMatchMode(self::SPH_MATCH_EXTENDED);

    if(!empty($this->_params['q'])) {
      $this->buildKeywordQuery();
    }

    if (!empty($this->_params['location-type']) && $this->_params['location-type'] == 'map') {
      $this->buildLocationQuery();
    }
    else {
      $this->setFilter('region_id', array($this->_params['region_id']));
      // ищем по району/метро/населённому пункту
      if (!empty($this->_params['regionnode'])) {
        $this->buildRegionNodeQuery();
      }
    }

    if (!empty($this->_params['images'])) {
      $this->setFilter('images',array(1));
      $this->setFilter('thumb',array(1));
    }

    if (isset($this->_params['created_at'])) {
      $this->setFilterRange(
        'created_at_ts',
        $this->_params['created_at']['from'],
        $this->_params['created_at']['to']
      );
    }
    
    $this->prepareVirtualFields(); //Transfer virtual fields to real first
    
    if (!empty($this->_params['price'])) {
      $this->buildPriceRange();
    }
    
    if (!empty($this->_params['field'])) {
      $this->buildFieldsSearch();
    }

    if (!empty($this->_params['sort'])) {
      $this->buildSortOrder();
    }
    else {
      $this->setSortMode(self::SPH_SORT_EXTENDED, 'rating desc, updated_at_ts desc');
    }

    if (!empty($this->_params['id'])) {
      $this->setFilter('lot_id', array($this->_params['id']), true);
    }

    return $this->query($this->_search_query, $index_type . '_main ' . $index_type . '_delta');
  }
  
  /**
   * Transfer virtual fields, like "square" in cottage type, to real 
   */
  protected function prepareVirtualFields(){
    if(!isset(Lot::$virtual_fields) || !isset(Lot::$virtual_fields[$this->_params['type']])) return false;
    
    foreach (Lot::$virtual_fields[$this->_params['type']] as $f => $d) {
      $depends_on = $d['depends_on'];
      unset( $d['depends_on'] );
      $this->prepareVirtualField($f, $depends_on, $d);
    }
  }

  /**
   * Convert virtual fields, like "square" in cottages, to field array
   * @param type $parameter Key of virtual parameter in params array, like "square"
   * @param type $main_key Key of field, by which fill be filter works, like 107 — cottage type
   * @param type $data Data array with from-to fields ids, like Lot::$cottage_type_square_links
   * @return boolean
   */
  protected function prepareVirtualField($parameter, $main_key, $data){
    if(empty($this->_params[$parameter]) || !is_array($data) || empty($data)) return false;

    $check_data = $this->_params[$parameter];
    unset( $this->_params[$parameter] );
    
    if(method_exists($this, 'prepare'.ucfirst($parameter).'Value'))
        $check_data = call_user_func(array($this, 'prepare'.ucfirst($parameter).'Value'), $check_data);

    if( is_int($main_key) ) { //It is field array key
      $main_data = empty($this->_params['field'][$main_key]) ? false : $this->_params['field'][$main_key];
    } else { //Maybe root array key?
      $main_data = empty($this->_params[$main_key]) ? false : $this->_params[$main_key];
    }
    //If no data, takes all possible values
    if(empty($main_data)) 
      $main_data = array_keys($data);
    else //Multi field?
      $main_data = isset($main_data['or']) ? $main_data['or'] : 
      ( isset($main_data['orlike']) ? $main_data['orlike'] : $main_data );
    
    foreach ($main_data as $v) {
      if(!in_array($v, array_keys($data))) continue;
      //Data array reference checking (only one level)
      $cd = $data[$v];
      if(!is_array($cd) && !empty($data[$cd]))
        $cd =  $data[$cd];
      if(empty($cd)) continue;
      
      if(!empty($check_data['from']))
        $this->_params['field'][$cd['from']]['from'] = $check_data['from'];
      
      if(!empty($check_data['to']))
        $this->_params['field'][$cd['to']]['to'] = $check_data['to'];
    }
  }

  protected function buildKeywordQuery()
  {
    Toolkit::logSection(sfConfig::get('sf_data_dir').'/search_keywords.txt', $this->_params['q']);
    $q = trim($this->_params['q']);
    if (strlen($q) > 0) {
      $q = str_replace(' ', '* ', $q);
      switch($this->_params['type']){
        case 'new_building-sale':
          $where = '@(address1,address2,description,f91) "*';
          break;
        case 'cottage-sale':
          $where = '@(address1,address2,description,f106) "*';
          break;
        default:
           $where = '@(address1,address2,description) "*';
      }
      
      $this->_search_query .= $where . $this->EscapeString($q) . '*"';
    }
  }

  /**
   * Converts price values depending on the currency
   * @param $price array('from' => number, 'to' => number) or one of keys
   * @return array array('from' => number, 'to' => number) or one of keys
   */
  protected function preparePriceValue($price)
  {
    $result = array();
    $currency = (!empty($this->_params['currency'])) ? $this->_params['currency'] : 'RUR';
    if (!array_key_exists($currency, Currency::$currencies)) {
      $currency = 'RUR';
    }
    if (isset($price['from'])) {
      $mod = $price['from'] < 100000 ? 100 : 1000;
      $price_from = Currency::convert($price['from'], $currency, 'RUR') - $mod;
      $price_from = ($price_from < 0) ? 0 : $price_from;
      $result['from'] = $price_from;
    }
    if (isset($price['to'])) {
      $mod = $price['to'] < 100000 ? 100 : 1000;
      $price_to = Currency::convert($price['to'], $currency, 'RUR') + $mod;
      $price_to = ($price_to < 0) ? 0 : $price_to;
      $result['to'] = $price_to;
    }
    
    return $result;
  }
  
  protected function buildPriceRange()
  {
    $price = $this->_params['price'];
    $price = $this->preparePriceValue($price);
    if(isset($price['from'])) $price_from = $price['from'];
    if(isset($price['to'])) $price_to = $price['to'];

    if ($this->_params['type'] == 'new_building-sale') {
      //Если заполнены и тот и другой фильтры
      if (isset($price_from) && isset($price_to)) {
        $this->setFilterFloatRange(
          'f70',
          $price_from,
          10000000000000
        );
        $this->setFilterFloatRange(
          'f71',
          $price_to,
          10000000000000,
          true
        );
      }
      //Если только один из фильтров
      else {
        if (isset($price_from)) {
          $this->setFilterFloatRange(
            'f70',
            $price_from,
            10000000000000
          );
        }
        if (isset($price_to)) {
          $this->setFilterFloatRange(
            'f71',
            0,
            $price_to
          );
        }
      }
    }
    else {
      if ($this->_params['type'] == 'commercial-rent') {
        if (!empty($this->_params['currency_type'])) {
          if (array_key_exists($this->_params['currency_type'], Lot::$currency_types['commercial-rent'])) {
            $currency_type = $this->_params['currency_type'];
          }
          else {
            $type = $this->_params['type'];
            $currency_type = Lot::$currency_default_type[$type];
          }

          $this->setFilterFloatRange(
            'price_' . $currency_type,
            isset($price_from) ? $price_from : 0,
            isset($price_to) ? $price_to : 1000000000000000
          );
        }
      } else {
        $this->setFilterFloatRange(
          'formated_price',
          isset($price_from) ? $price_from : 0,
          isset($price_to) ? $price_to : 1000000000000000
        );
      }
    }
  }

  /**
   * build query for fields search
   */
  protected function buildFieldsSearch()
  {
    $custom_filters = array(); // For $this->SetSelect
    foreach ($this->_params['field'] as $id => $value) {
      if (!is_array($value)) {
        if (is_int($value)) {
          $this->setFilter('f' . $id, array($value));
        }
        else {
          $this->_search_query .= ' @f' . $id . ' "' . $this->EscapeString($value) . '"';
        }
      }
      else if (isset($value['from']) || isset($value['to'])) {
        if ( in_array($id, array(72,73)) && $this->_params['type'] == 'new_building-sale') {
          if (!empty($this->_params['field'][72]['from']) && !empty($this->_params['field'][73]['to'])) {
            $this->setFilterRange(
              'f72',
              floatval($this->_params['field'][72]['from']),
              floatval($this->_params['field'][73]['to'])
            );
            $this->setFilterRange(
              'f73',
              floatval($this->_params['field'][72]['from']),
              100000000000
            );
          } else {
            if ($id == 72) {
              $this->setFilterRange(
                'f72',
                (isset($value['from'])) ? floatval($value['from']) : 0,
                10000000
              );
            }
            if ($id == 73) {
              $this->setFilterRange(
                'f73',
                (isset($this->_params['field'][72]['from'])) ? floatval($this->_params['field'][72]['from']) : 0,
                100000000
              );
            }
          }
        }elseif($this->_params['type'] == 'cottage-sale') {
          switch ($id) {
            //Area square
            case 94:
            case 95:
              $is_from = isset($this->_params['field'][$id]['from']);
              $cf = $is_from ? $id+1 : $id - 1;
              $min = $is_from ? (isset($this->_params['field'][$id]['from']) ? floatval($this->_params['field'][$id]['from']) : 0) : 0;
              $max = $is_from ? 100000000 : (isset($this->_params['field'][$id]['to']) ? floatval($this->_params['field'][$id]['to']) : 100000000);
              $this->SetFilterFloatRange('f'.$cf, $min, $max);
              if(empty($custom_filters['asq'])) $custom_filters['asq'][$id] = "(f$id > 0 AND $cf > 0)";
              break;
            
            //Price
            case 96:
            case 97:
            case 100:
            case 101:
            case 104:
            case 105:
              //Identify the orientation of the field
              $is_from = $this->array_search_recursive($id, Lot::$virtual_fields);
              $is_from = in_array('from', $is_from) ? true : false;
              //Calculation of the expression
              $compare = $is_from ? '>=' : '<=';
              $cf = $is_from ? $id+1 : $id-1;
              $value = $is_from ? $value['from'] : $value['to'];
              $custom_filters['pf'][$id] = "((f$cf = 0 AND f$id > 0) OR (f$cf $compare $value AND f$cf > 0))";
              break;
            
            //Square
            case 98:
            case 99:
            case 102:
            case 103:
              //Identify the orientation of the field
              $is_from = $this->array_search_recursive($id, Lot::$virtual_fields);
              $is_from = in_array('from', $is_from) ? true : false;
              //Calculation of the expression
              $compare = $is_from ? '>=' : '<=';
              $cf = $is_from ? $id+1 : $id-1;
              $value = $is_from ? $value['from'] : $value['to'];
              $custom_filters['sqf'][$id] = "((f$cf = 0 AND f$id > 0) OR (f$cf $compare $value AND f$cf > 0))";
              break;
            
            default:
              $this->SetFilterFloatRange( //Standart
                'f' . $id,
                (isset($value['from'])) ? floatval($value['from']) : 0,
                (isset($value['to'])) ? floatval($value['to']) : 10000000
              );
              break;
          }
        } else {
          if ($this->_params['type'] == 'new_building-sale' || $this->_params['type'] == 'cottage-sale') break;
          $this->setFilterRange(
            'f' . $id,
            (isset($value['from'])) ? floatval($value['from']) : 0,
            (isset($value['to'])) ? floatval($value['to']) : 10000000
          );
        }
      }
      else if (isset($value['or']) || isset($value['orlike'])) {
        $or_vals = isset($value['or']) ? $value['or'] : $value['orlike'];
        $or_values = array();
        foreach ($or_vals as $or_val) {
          if (preg_match('/^([5-9]|\d{2,})/', $or_val)) {
            $or_values = array_merge($or_values, range(5, 20));
          }
          else {
            //Exact matching for cottage-sale 107 field http://dev.garin.su/issues/16050#note-56
            if($this->_params['type'] == 'cottage-sale' && $id == 107)
              $or_values[] = '^' . $this->EscapeString($or_val) . '$';
            else
              $or_values[] = '"*' . $this->EscapeString($or_val) . '*"';
          }
        }

        $this->_search_query .= ' @f' . $id . ' ' . implode(' | ', $or_values);
      }
      else {
        $values = array();
        foreach ($value as $v) {
          if (is_int($v)) {
            $this->setFilter('f' . $id, array('"' . $v . '"'));
          }
          else {
             $values[] = '"' . $this->EscapeString($v) . '"';
          }
        }

        if (count($values) > 0) {
          $this->_search_query .= ' @f' . $id . ' ' . implode(' | ', $values);
        }
      }
    }
    
    if(!empty($custom_filters)) {
      array_unshift($custom_filters, $this->select);
      foreach ($custom_filters as $f => $d) {
        if(!is_array($d)) continue;
        foreach(array_keys($d) as $v) {
          if(isset($d[$v]) && !preg_match('#^\(*f(\d+)#', $d[$v], $matches)) continue;
          if($v != intval($matches[1]) && isset($d[$matches[1]])){ //AND condition
            $d[$v] = '('.$d[$v].' AND '.$d[$matches[1]].')';
            unset($d[$matches[1]]);
          }
        }

        $custom_filters[$f] = 'IF('.implode(' OR ', $d).',5,0) AS '.$f;
        $this->SetFilter($f, array(5));
      }
      $this->SetSelect( implode(', ', $custom_filters) );
    }
  }

  /**
   * build query for coords search
   */
  protected function buildLocationQuery()
  {
    $lat = (!empty($this->_params['latitude'])) ? $this->_params['latitude'] : false;
    $lng = (!empty($this->_params['longitude'])) ? $this->_params['longitude'] : false;
    if (!$lat || !$lng) {
      list($lat, $lng) = Doctrine::getTable('Region')->find($this->_params['region_id'])->default_search_coords;
    }
    $this->setFilterFloatRange('latitude', $lat['from'], $lat['to']);
    $this->setFilterFloatRange('longitude', $lng['from'], $lng['to']);

    if (!empty($this->_params['restrict_region']) && $this->_params['restrict_region']) {
      $this->setFilter('region_id', array($this->_params['region_id']));
    }
  }

  /**
   * set sort order
   */
  protected function buildSortOrder()
  {
    $sort     = explode('-', $this->_params['sort']);
    $orderby  = self::SPH_SORT_EXTENDED;

    # defaults
    $sortby   = 'rating';
    $orderby = self::SPH_SORT_ATTR_DESC;

    if (count($sort) == 2) {
      $order = !in_array($sort[1], array('asc', 'desc')) ? 'asc' : $sort[1];
      $orderby = ($order == 'desc') ? self::SPH_SORT_ATTR_DESC : self::SPH_SORT_ATTR_ASC;

      switch ($sort[0]) {
        case 'address':
          $orderby = self::SPH_SORT_EXTENDED;
          $sortby = "address1 $order, address2 $order";
          break;
        case 'size':
          switch($this->_params['type']) {
            case 'commercial-sale':
              $orderby  = self::SPH_SORT_EXTENDED;
              $sortby = 'f46 ' . $order .', f47 ' . $order;
              break;
            case 'commercial-rent':
              $sortby = 'f46';
              break;
            case 'apartament-sale':
              $sortby = 'f1';
              break;
            case 'apartament-rent':
              $sortby = 'f1';
              break;
            case 'new_building-sale':
              $orderby  = self::SPH_SORT_EXTENDED;
              $sortby = 'f72 ' . $order .', f73 ' . $order;
              break;
          }
          break;
        case 'price':
          if ($this->_params['type'] == 'new_building-sale') {
            $orderby  = self::SPH_SORT_EXTENDED;
            $sortby = 'f70 ' . $order .', f71 ' . $order;
          }
          else {
            $sortby = 'formated_price';
          }
          break;
        case 'date':
          $sortby = 'updated_at_ts';
          break;
        case 'seller':
          $sortby = 'company_name';
          break;
        case 'rating':
          $sortby  = "rating {$order}, updated_at_ts {$order}";
          $orderby = self::SPH_SORT_EXTENDED;
          break;
      }
    }

    $this->setSortMode($orderby, $sortby);
  }

  /**
   * build nodes area for search
   */
  protected function buildRegionNodeQuery()
  {
    $search_string = '';
    foreach ($this->_params['regionnode'] as $id => $node) {
      @list($regionnode, $socr) = Regionnode::unformatName($node);
      $search_string .= (($id > 0)? ' | ': '') . '"' . $regionnode . '"';
    }

    $this->_search_query .= sprintf(' @address1 %s ', $search_string);
  }

  /**
   * news portal build search query
   * @param string $q
   * @return $query || false
   */
  public function searchNewsPortal($q = '')
  {
    $indexes = array(
      'news_main', 'news_delta', 'events_main',
      'events_delta', 'article_main', 'article_delta',
      'analytics_main', 'analytics_delta', 'qa_main', 'qa_delta'
    );
    if ($q != '') {
      $q = trim(str_replace(' ', '* ', $q));
      return $this->query($this->EscapeString($q) . '*', implode(' ', $indexes));
    }
    return false;
  }

  /**
   * author articles & expert articles  build search query
   * @param string $q
   * @return $query || false
   */
  public function searchAuthorArticles($q = '')
  {
    $indexes = array(
      'author_article_main', 'author_article_delta',
      'expert_article_main', 'expert_article_delta'
    );

    if ($q != '') {
      $q = trim(str_replace(' ', '* ', $q));
      return $this->query($this->EscapeString($q) . '*', implode(' ', $indexes));
    }
    return false;
  }

  /**
   * blog posts build search query
   * @param string $q
   * @return $query || false
   */
  public function searchBlogs($q = '')
  {
    $indexes = array(
      'blog_main', 'blog_delta'
    );

    if ($q != '') {
      $q = trim(str_replace(' ', '* ', $q));
      return $this->query($this->EscapeString($q) . '*', implode(' ', $indexes));
    }
    return false;
  }

  public function getNearestStreets($q, $region_id, $regionnode = null) {
    $lat = 0;
    $lng = 0;
    if ($q) {
      $this->SetFilter('region_id', array(77));
      if ($regionnode) {
        $this->SetFilter('node_id', array($regionnode));
      }
      $this->SetSortMode(self::SPH_SORT_RELEVANCE);
      $r = $this->query('@name ' . $q, 'streets');
      if (!isset($r['matches'][0]))
        return false;
      $street = $r['matches'][0]['attrs'];
      $lat = $street['latitude'];
      $lng = $street['longitude'];
    } else {
      $r = Doctrine::getTable('Region')->findOneById($region_id);
      if (!$r) {
        return false;
      }
      $lat = deg2rad($r->latitude);
      $lng = deg2rad($r->longitude);
    }
    $this->ResetFilters();
    $this->SetGeoAnchor('latitude', 'longitude', $lat, $lng);
    $this->SetFilterFloatRange('@geodist', 0.0, 10000);
    $this->SetSortMode(self::SPH_SORT_EXTENDED, '@geodist asc');
    $this->SetMatchMode(self::SPH_MATCH_ALL);
    $this->SetLimits(0,12);
    $r = $this->query('', 'streets');
    if (!isset($r['matches'])) {
      return false;
    }
    $ret = array();
    foreach ($r['matches'] as $match) {
      $ret[] = array(
          'region_id' => $match['attrs']['region_id'],
          'regionnode_name' => $match['attrs']['regionnode_name'],
          'regionnode_id' => $match['attrs']['regionnode_id'],
          'street' => $match['attrs']['street'],
          'socr' => $match['attrs']['socr'],
          );
    }
    return $ret;
  }

  public function searchOneLot($params) {
    $this->ResetFilters();
    $this->ResetGroupBy();
    $this->ResetOverrides();
    $this->_search_query = '';

    $this->_params = $params;
    $index_type = str_replace('-', '_', $this->_params['type']);

    $this->setFilter('type', array(Lot::$types[$this->_params['type']]));
    $this->setFilter('status', array(Lot::$statuses['active']));
    $this->setFilter('region_id', array($this->_params['region_id']));
    $this->setFilterRange('created_at_ts', 0, date('U'));
    $this->setFilterRange('active_till_ts', 0, date('U', strtotime('-1 hour')), true);
    $this->setMatchMode(self::SPH_MATCH_EXTENDED);
    $this->setSortMode(self::SPH_SORT_EXTENDED, '@random');

    return $this->query($this->_search_query, $index_type . '_main ' . $index_type . '_delta');
  }

  public function EscapeString($string)
  {
    $from = array('\\', '(',')','|','-','!','@','~','"','&', '/', '^', '$', '=');
    $to   = array('\\\\', '\(','\)','\|','\-','\!','\@','\~','\"', '\&', '\/', '\^', '\$', '\=');

    return str_replace($from, $to, $string);
  }

  public function searchSimilar($params) {
    $this->ResetFilters();
    $this->ResetGroupBy();
    $this->ResetOverrides();
    $this->_search_query = '';

    $this->_params = $params;
    
    if (!empty($this->_params['regionnode'])) {
      $this->_search_query = '@address1 ';
      foreach ($this->_params['regionnode'] as $region_node) {
        $this->_search_query .= '(';
        foreach (explode("#", $region_node) as $node) {
          if ('' == trim($node)) continue;
          $this->_search_query .= '(' . $this->EscapeString($node) . ' | *' . $this->EscapeString($node) . '*) & ';
        }
        $this->_search_query = mb_substr($this->_search_query, 0, -3) . ') | ';
      }
      $this->_search_query = mb_substr($this->_search_query, 0, -2);
    }
    
    //Houses special
    if($this->_params['type'] == 'house-sale' || $this->_params['type'] == 'house-rent')
    {
      $cf = array();
      if(!empty($this->_params['field']['26'])) {
        $cf[] = "(f26 >= {$this->_params['field']['26']['from']} AND f26 <= {$this->_params['field']['26']['to']})";
      }
      
      if(!empty($this->_params['field']['27'])) 
        $cf[] = "(f27 >= {$this->_params['field']['27']['from']} AND f27 <= {$this->_params['field']['27']['to']})";
        
      if(!empty($cf)){
        $this->SetSelect('*, IF('.implode(' OR ', $cf).',1,0) AS cf' );
        $this->SetFilter('cf', array(1));
        unset( $this->_params['field']['26'], $this->_params['field']['27'] );
      }
    }
    
    //Houses special
    if($this->_params['type'] == 'house-sale' || $this->_params['type'] == 'house-rent')
    {
      $cf = array();
      if(!empty($this->_params['field']['26'])) {
        $cf[] = "(f26 >= {$this->_params['field']['26']['from']} AND f26 <= {$this->_params['field']['26']['to']})";
      }
      
      if(!empty($this->_params['field']['27'])) 
        $cf[] = "(f27 >= {$this->_params['field']['27']['from']} AND f27 <= {$this->_params['field']['27']['to']})";
        
      if(!empty($cf)){
        $this->SetSelect('*, IF('.implode(' OR ', $cf).',1,0) AS cf' );
        $this->SetFilter('cf', array(1));
        unset( $this->_params['field']['26'], $this->_params['field']['27'] );
      }
    }

    if ($this->_params['type'] == 'commercial-sale' || $this->_params['type'] == 'commercial-rent' && is_null($this->_params['field']['45'])) {
      $this->_search_query .= '@f45 (';

      foreach (explode(', ', $this->_params['field']['45']) as $row) {
        $this->_search_query .= '"' . $this->EscapeString($row) . '" | ';
      }
      $this->_search_query = mb_substr($this->_search_query, 0, -3) . ')';

      unset($this->_params['field'][45]);
    }

    $index_type = str_replace('-', '_', $this->_params['type']);

    $this->setFilter('type', array(Lot::$types[$this->_params['type']]));
    $this->setFilter('status', array(Lot::$statuses['active']));
    $this->setFilter('region_id', array($this->_params['region_id']));
    $this->setFilter('lot_id', array($this->_params['id']), true);
    
    $this->prepareVirtualFields(); //Transfer virtual fields to real first

    if (!empty($this->_params['price'])) {
      $this->buildPriceRange();
    }

    if (!empty($this->_params['field'])) {
      $this->buildFieldsSearch();
    }

    $this->setMatchMode(self::SPH_MATCH_EXTENDED2);
    $this->setSortMode(self::SPH_SORT_EXTENDED, 'id DESC');
    
    return $this->query($this->_search_query, $index_type . '_main ' . $index_type . '_delta');
  }

  /**
   *
   * @param string $params
   * @return type 3
   */
  public function getLandingPages($params) {
    $this->ResetFilters();
    $this->ResetGroupBy();
    $this->ResetOverrides();
    $this->_search_query = '';

    if(isset($params['url'])) {
      if (isset($params['region_id'])) {
        $this->setFilter('region_id', array($params['region_id']));
      }
      $this->_search_query = '@url ^'.$this->EscapeString($params['url']).'$ @type ='.$this->EscapeString($params['type']);
    } elseif(isset($params['params'])) {
      ksort($params['params']);
      $this->_search_query = '@hash ' . $this->EscapeString(md5(serialize($params['params'])));
    } elseif(isset($params['level'])){
      $this->setFilter('region_id', array($params['region_id']));
      $this->_search_query .= " @type " . $params['mode'];
      
      switch ($params['level']) {
        case 'region':
          $this->_search_query .= ' @params region_id -(regionnode) -(q_text)';
          break;
        
        case 'district':
        case 'city':
          $after = !empty($params['node']) ? ' << "'. $this->EscapeString($params['node']) .'"' : '';
          $this->_search_query .= " @params =regionnode$after";
          break;
        
        case 'street':
          $after = !empty($params['street']) ? ' << "'. $this->EscapeString($params['street']) .'"' : '';
          $after .= (!empty($params['node']) ? ' << =regionnode << ' . $this->EscapeString($params['node']) : '');
          $this->_search_query .= " @params =q_text$after";
          break;
      }
    } else {
      return array();
    }

    $this->setMatchMode(self::SPH_MATCH_EXTENDED2);
    return $this->query($this->_search_query, 'landing_pages');
  }
  
  public function ListLandingPages($params)
  {
    $this->ResetFilters();
    $this->ResetGroupBy();
    $this->ResetOverrides();
    $this->_search_query = '';
    
    $this->_params = $params;
    if (!empty($this->_params['type'])) {
      $this->_search_query .= '@type "' . $this->EscapeString($this->_params['type']) . '" ';
    }
    
    if (!empty($this->_params['region_id'])) {
      $this->buildLandingForRegion();
    }
    
    $this->SetMatchMode(self::SPH_MATCH_EXTENDED2);
    $this->setSortMode(self::SPH_SORT_EXTENDED, 'region_id DESC, h1 ASC');
    
    return $this->query($this->_search_query, 'landing_pages');
  }
  
  protected function buildLandingForRegion()
  {
    if(!empty($this->_params['region_id'])) {
      $this->setFilter('region_id', array($this->_params['region_id']));
      
      if (!empty($this->_params['type'])){
        switch($this->_params['type']) {
          case 'apartament-sale':
          case 'apartament-rent':
          case 'commercial-sale':
          case 'commercial-rent':
            if (in_array($this->_params['region_id'], array(77, 78))) {
              $this->_search_query .= '@params =regionnode << *' . $this->EscapeString('м. ') . '* -(q_text)';
            }
            else {
              $this->_search_query .= '@params =regionnode << *' . $this->EscapeString('г. ') . '* -(q_text)';
            }
            break;
          case 'house-sale':
          case 'house-rent':
            $this->_search_query .= '@params =regionnode << *' . $this->EscapeString('р-н') . '* -(q_text)';
            break;
          case 'new_building-sale':
            break;
        }
      }
    }
  }
  
  public function getOneLandingPage($params)
  {
    $this->ResetFilters();
    $this->ResetGroupBy();
    $this->ResetOverrides();
    $this->_search_query = '';
    
    $this->setFilter('region_id', array($params['region_id']));
    $this->_search_query .= ' @type "' . $this->EscapeString($params['type']) . '"';
    
    $search   = array();
    if (!empty($params['field'])) {
      $search[] = '=field << "'. $this->EscapeString($params['field']) .'"';
    }
    if (!empty($params['nodes'])) {
      $nodes = array();
      foreach ($params['nodes'] as $node) {
        $nodes[] = '"' . $this->EscapeString($node) . '"';
      }
      $search[] = ' =regionnode << ' . implode('|', $nodes);
    }
    
    if (count($search) > 0) {
      $this->_search_query .= ' @params ' . implode(' ', $search);
    }
    
    $this->setMatchMode(self::SPH_MATCH_EXTENDED2);
    return $this->query($this->_search_query, 'landing_pages');
  }

  public function getGeoLandings($region_id, $type, $coords = array(), $landing_ids = array(), $no_root = false) {
    $this->ResetFilters();
    $this->ResetGroupBy();
    $this->ResetOverrides();
    $this->_search_query = '';
    $lat = 0;
    $lng = 0;
    if (count($coords) == 2) {
      list($lat, $lng) = $coords;
      $lat = deg2rad($lat);
      $lng = deg2rad($lng);
    } else {
      $r = Doctrine::getTable('Region')->findOneById($region_id);
      if (!$r) {
        return false;
      }
      $lat = deg2rad($r->latitude);
      $lng = deg2rad($r->longitude);
    }
    if (count($landing_ids) > 0) {
      $this->setFilter('landing_id', $landing_ids, true);
    }
    $this->SetFilter('region_id', array($region_id));
    $this->SetGeoAnchor('latitude', 'longitude', $lat, $lng);
    
    $distance = in_array($region_id, array(77,78)) ? 10000 : 30000;
    $this->SetFilterFloatRange('@geodist', 0.0, $distance);

    $this->SetSortMode(self::SPH_SORT_EXTENDED, '@weight DESC @geodist DESC');
    $this->SetMatchMode(self::SPH_MATCH_ALL);
    if($no_root) 
      $this->SetLimits(0,7); 
    else 
      $this->SetLimits(0,6);
    $this->_search_query .= ' @type "' . $this->EscapeString($type) . '"';
    $r = $this->query($this->_search_query, 'landing_pages');
    if (!isset($r['matches'])) return false;
    $ret = array();
    foreach ($r['matches'] as $match) {
      if($no_root && $match['attrs']['url'] == 'root') continue;
      if(count($ret)>6) break;
      $ret[] = array(
        'id'   => $match['attrs']['landing_id'],
        'url'  => $match['attrs']['url'],
        'h1'   => $match['attrs']['h1'],
        'type' => $match['attrs']['type'],
      );
    }
    return $ret;
  }
  
  private function array_search_recursive( $needle, $haystack, $strict=false, $path=array() )
  {
    if( !is_array($haystack) ) {
      return false;
    }

    foreach( $haystack as $key => $val ) {
      if( is_array($val) && $subPath = $this->array_search_recursive($needle, $val, $strict, $path) ) {
        $path = array_merge($path, array($key), $subPath);
        return $path;
      } elseif( (!$strict && $val == $needle) || ($strict && $val === $needle) ) {
        $path[] = $key;
        return $path;
      }
    }
    return false;
  }
}
