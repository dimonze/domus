<?php

/**
 * lot components.
 *
 * @package    domus
 * @subpackage lot
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class lotComponents extends sfComponents
{
  public function executeSimilar() {
    $this->type = $this->getRequest()->getParameter('type', null);
    $similar = array();
    
    if (is_null($this->type)){
      $this->similar = $similar;
      return true;
    }
    
    /* Address */
    $address = explode(', ', $this->lot->address1);
    $address_p = array();
    if (count($address) > 1) {
      foreach (array_slice($address, 1) as $line) {
        $line1 = Regionnode::unformatName($address[0]);
        $line = Regionnode::unformatName($line);
        $address_p[] = $line1[0] . '#' . $line[0];
      }
    }
    else {
      $address[0] = Regionnode::unformatName($address[0]);
      $address_p[] = $address[0][0];
    }
    
    //Fields
    $similar_fields = Lot::$_similar_fields[$this->lot->type];
    $fields = array();
    foreach ($similar_fields as $field) {
      $field_val = $this->lot->getLotInfoField($field);
      if(!is_null($field_val)){
        $fields[$field] = array(
            'from' => $field_val * 0.9,
            'to' => $field_val * 1.1
        );
      }
    }

    if ($this->type == 'commercial-sale' || $this->type == 'commercial-rent'){
      $fields['45'] = $this->lot->getLotInfoField(45);
    }
    
    if ($this->type == 'cottage-sale'){
      $fields['107'] = $this->lot->getLotInfoField(107);
      $fields['107'] = preg_split('#\s*,\s*#', $fields['107']);
    }
    
    // Searching... 
    $options = array(
      'limit'   => !empty($this->limit) ? $this->limit : 4,
      'offset'  => 0
    );
    
    $params = array(
      'type'      => $this->type,
      'region_id' => $this->getUser()->current_region->id,
      'id' => $this->lot->id,
      'regionnode' => $address_p,
      'price' => array(
          'from' => $this->lot->getPriceExchanged('RUR') * 0.9,
          'to' => $this->lot->getPriceExchanged('RUR') * 1.1
      ),
      'field' => $fields
    );
    
    $this->sphinx = new DomusSphinxClient($options);
    $this->sphinx->searchSimilar($params);
    
    $pager = new DomusSphinxSearchPager('Lot', $options['limit'], $this->sphinx);
    $pager->setPage(1);
    $pager->init();
    
    $this->page = $pager->getPage();
    $this->nb_pages = $pager->getLastPage();
    $this->similar = $pager->getResults();
  }
  
  public function executeSimilarNodes()
  {
    $params = array(
      'type'        => $this->type,
      'region_id'   => $this->lot->region_id,
      'id'          => $this->lot->id,
    );
    
    if ($this->check_geo) {
      $address = explode(', ', $this->lot->address1);
      $nodes = array();
      if (count($address) > 1) {
        foreach (array_slice($address, 1) as $node) {
          $rnode = Regionnode::unformatName($node);
          if (in_array($rnode[1], array('г.', 'м.'))) {
            $nodes[] = $node;
          }
        }
      }
      else {
        $node = Regionnode::unformatName($address[0]);
        if (in_array($node[1], array('г.', 'м.'))) {
          $nodes[] = $address[0];
        }
      }
      $params['regionnode'] = $nodes;
    }
    
    if ($this->check_price) {
      $min_price = (substr($this->lot->getLotInfoField(70), 3) * $this->lot->exchange) - 30000;
      $min_price = ($min_price < 0) ? 0 : $min_price;
      $max_price = (substr($this->lot->getLotInfoField(71), 3) * $this->lot->exchange) + 30000;
      
      $price = array(
        'from'  =>  $min_price,
        'to'    =>  $max_price
      );
      
      $params['price'] = $price;
    }
    
    $options = array(
      'limit'   => !empty($this->limit) ? $this->limit : 4,
      'offset'  => 0
    );
    
    $this->sphinx = new DomusSphinxClient($options);
    $this->sphinx->searchSimilar($params);
    
    $pager = new DomusSphinxSearchPager('Lot', $options['limit'], $this->sphinx);
    $pager->setPage(1);
    $pager->init();
    
    $this->page     = $pager->getPage();
    $this->nb_pages = $pager->getLastPage();
    $this->lots     = $pager->getResults();
    
    if ($this->check_geo) {
      //Search Landing Page
      $params = array(
        'region_id' =>  $this->lot->region_id,
        'type'      =>  $this->lot->type,
        'nodes'     =>  $nodes
      );
      $options = array(
        'maxmatches'  => 1
      );

      $this->sphinx = new DomusSphinxClient($options);
      $landing_page = $this->sphinx->getOneLandingPage($params);
      if (!empty($landing_page['matches'])) {
        $title = 'Похожие новостройки';
        $lparams = unserialize($landing_page['matches'][0]['attrs']['params']);
        $node = array_intersect($lparams['regionnode'], $nodes);
        $node = Regionnode::unformatName($node[0]);
        if ($node[1] == 'г.') {
          $title .= ' в ' . WordInflector::get($node[0], WordInflector::TYPE_PREPOSITIONAL);
        }
        elseif ($node[1] == 'м.'){
          $title .= ' у метро ' . $node[0];
        }

        sfContext::getInstance()->getConfiguration()->loadHelpers('DomusForm');
        if ($node[0]) {
          $this->h2 = link_to($title, Toolkit::getGeoHostByRegionId($landing_page['matches'][0]['attrs']['region_id'], false) . '/' . $landing_page['matches'][0]['attrs']['url']);
        }
        else {
          $this->h2 = link_to($title, Toolkit::getGeoHostByRegionId($landing_page['matches'][0]['attrs']['region_id'], false));
        }
      }
    }
    
    if ($this->check_price) {
      $this->h2 = 'Похожие новостройки по цене';
    }
  }

  public function executeList() {
    if (!isset($this->type)){
      return false;
    }

    $options = array(
      'limit'   => !empty($this->limit) ? $this->limit : 4,
      'offset'  => 0
    );
    $params = array(
      'type'      => $this->type,
      'region_id' => !empty($this->region_id) ? $this->region_id :$this->getUser()->current_region->id,
      'images'    => true,
    );

    if (!empty($this->lot)) {
      $params['id'] = $this->lot->id;
    }
    
    if(isset($this->params)) $params = array_merge($params,$this->params);
    unset($params['images']);
    $this->sphinx = new DomusSphinxClient($options);
    $this->sphinx->search($params);

    $result = $this->sphinx->getRes();
    if (empty($result['matches']) || count($result['matches']) != 4) {
      return false;
    }
    else {
      $pager = new DomusSphinxSearchPager('Lot', $options['limit'], $this->sphinx);
      $pager->setPage(1);
      $pager->init();

      $this->page = $pager->getPage();
      $this->nb_pages = $pager->getLastPage();
      $this->lots = $pager->getResults();
      $this->types = sfConfig::get('app_lot_types');
    }
  }

  public function executeActions() {
    $user = $this->getUser();
    $this->items = array();
    $current_url = $this->getRequest()->getPathInfo();

    foreach ($this->actions as $action)
    {
      $url = false;
      $noindex = false;

      switch ($action) {
        case 'map':
          $url = $current_url;
          $title = 'Показать на карте';
          $attr = array('class' => 'show-map action_04');
          break;

        case 'print':
          $url = $current_url;
          $title = 'На печать';
          $attr = array('class' => 'print action_06');
          break;

        case 'compare':
          $url = '@compare_action?action=toggle&id=' . $this->lot->id;
          $noindex = true;
          $title = 'В список сравнения';
          $attr = array(
              'class' => 'post-toggle action_03',
              'active' => 'Из списка сравнения',
              'inactive' => 'В список сравнения',
            );
          if ($this->getUser()->compareIsset($this->lot->id)) {
            $attr['class'] .= ' action_03-active';
            $title = 'Из списка сравнения';
          }
          break;

        case 'compare-delete':
          $url = '@compare_action?action=toggle&id=' . $this->lot->id;
          $noindex = true;
          $title = 'Удалить из списка сравнения';
          $attr = array('class' => 'action_05');
          break;

        case 'notify':
          $url = '@lot_action?action=notify&id=' . $this->lot->id;
          $noindex = true;
          $title = 'Оповещать об изменении цены';
          $attr = array('class' => 'popup action_02', 'rel' => 'auth');
          break;

        case 'claim':
          $noindex = true;
          if($user->isLotClaimed($this->lot->id)) {
            $url = url_for('show_lot', $this->lot);
            $title = 'Вы уже жаловались на это объявление';
            $attr = array('class' => 'action_02');
          }
          else {
            $url = '@lot_action?action=claim&id=' . $this->lot->id;
            $title = 'Пожаловаться на объявление';
            $attr = array('class' => 'popup action_02 claim', 'rel' => 'reg');
          }
          break;

        case 'favourite':
          $noindex = true;
          if (!$user->isAuthenticated()) {
            $attr = array('class' => 'popup action_01', 'rel' => 'auth');
            $title = 'В избранное';
            $url = 'user/login';
            break;
          }

          $url = '@lot_action?action=favourite&id=' . $this->lot->id;
          $title = 'В избранное';
          $attr = array(
              'class' => 'post-toggle action_01',
              'active' => 'Из избранного',
              'inactive' => 'В избранное',
            );

          if ($user->isLotFavourite($this->lot)) {
            $title = 'Из избранного';
            $attr['class'] .= ' action_01-active';
          }
          break;
      }

      if ($url) {
        $this->items[] = array(
          'title'   => $title,
          'url'     => $url,
          'attr'    => array_merge($attr, array('title' => $title, 'lot' => $this->lot->id)),
          'noindex' => $noindex,
        );
      }
    }

  }

  public function executeLotscount()
  {
    if (!isset($this->type) || !in_array($this->type, array_keys(sfConfig::get('app_lot_types')))){
      return false;
    }
    $user = $this->getUser();
    $this->lots_nb = Doctrine_Query::create()
      ->from('Lot l')
      ->select('COUNT(l.id) as count')
      ->andWhere('l.type = ?', $this->type)
      ->andWhere('l.region_id = ?', $this->getUser()->current_region->id)
      ->andWhere('l.status = ?', 'active')
      ->andWhere('l.deleted_at = ? OR l.deleted_at IS NULL', 0)
      ->setHydrationMode(Doctrine::HYDRATE_NONE)
      ->fetchOne();
    $this->lots_nb = $this->lots_nb[0];
  }
  
  public function executeAdditNbInfo()
  {
    $sphinx = new DomusSphinxClient(array('offset' => 0, 'limit' => 1));
    
    $replacments = array(
        '/^м. /' => 'у метро '
        ,'/^п. /' => ''
        ,'/^д. /' => ''
        ,'/^(.+)\s*мрн.$/' => 'в микрорайоне $1'
        ,'/^район /' => 'в районе '
        ,'/^г. /' => 'в городе '
        ,'/^пгт /' => 'в пгт '
        ,'/^(.+)\s*р\-н/eu' => "'в '.WordInflector::get('$1', WordInflector::TYPE_PREPOSITIONAL).' районе'"
    );
    
    
    $search_query = '';
    $sphinx->SetFilter('type', array( Lot::$types[$this->type] ));
    $sphinx->SetFilter('region_id', array($this->region->id));
    $sphinx->setMatchMode(DomusSphinxClient::SPH_MATCH_EXTENDED);
    $sphinx->SetSelect('MIN(f70) as minprice, MIN(f72) as minsquare, 1 as one');
    $sphinx->SetFilterFloatRange('f70', 0, 0, true);
    $sphinx->SetFilterFloatRange('f72', 0, 0, true);
    $sphinx->setGroupBy("one",DomusSphinxClient::SPH_GROUPBY_ATTR,'id ASC');
    $sphinx->setRankingMode(DomusSphinxClient::SPH_RANK_NONE);
    
    //Prepare nodes
    if(!empty($this->regionnode)) {
      $search_string = '';
      foreach ($this->regionnode as $id => $node) {
        @list($regionnode, $socr) = Regionnode::unformatName($node);
        $search_string .= (($id > 0)? ' | ': '') . '"' . $regionnode . '"';
      }
      $search_query .= sprintf(' @address1 %s ', $search_string);
      
      //Get full socr for all nodes
      foreach ($this->regionnode as &$regionnode) {
        $regionnode = preg_replace(array_keys($replacments), array_values($replacments), $regionnode);
      }
    }
    $this->regionnode = implode(', ', $this->regionnode);
    
    $results = $sphinx->Query($search_query, 'new_building_sale_main new_building_sale_delta');
    $this->minprice = $results['matches'][0]['attrs']['minprice'];
    $this->minsquare = $results['matches'][0]['attrs']['minsquare'];
  }
}
