<?php

/**
 * search actions.
 *
 * @package    domus
 * @subpackage search
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class searchActions extends sfActions {

  public function postExecute()
  {
    MetaParse::setMetas($this);
  }

  public function executeIndex(sfWebRequest $request)
  {
    //No "root" slug!!!
    if($request->hasParameter('slug') && $request->getParameter('slug') == 'root')
      $this->forward404();
    //#14827
    if((sfConfig::get('is_new_building') && Lot::getRealType($request->getParameter('type')) != 'new_building-sale')
      || (sfConfig::get('is_cottage') && Lot::getRealType($request->getParameter('type')) != 'cottage-sale'))
      $this->forward404();
    
    if ($request->isXmlHttpRequest() && $request->hasParameter('hash')) {
      $this->forward('search', 'get');
    }

    if (preg_match('/(sale|rent)/', $request->getParameter('type'))) {
      $params = $request->getParameterHolder()->getAll();
      $this->redirect(str_replace('+', '-', DomusSearchRoute::buildUrlForRedirect($params)), 301);
    }

    try {
      //Поиск дефолтной посадочной для региона и типа недвижимости
      if(!$request->hasParameter('slug') && !$request->isXmlHttpRequest()
        && (
          !$request->getCookie('landing_hash')
          || in_array($request->hasParameter('type'), array_keys(Lot::$_routing_types))))
      {
        $options = array(
          'limit'       => 1,
          'maxmatches'  => 1,
          'offset'      => 0
        );
        $params = array(
          'url'       => 'root',
          'region_id' => $this->getUser()->current_region->id,
          'type'      => Lot::getRealType($request->getParameter('type'))
        );

        $this->sphinx = new DomusSphinxClient($options);
        $this->sphinx->getLandingPages($params);
        $result = $this->sphinx->getRes();
        if(!empty($result['matches'])) { //Посадочная страница
          $landing_page = $result['matches'][0]['attrs'];
          //Выгружаем параметры запроса
          $params = unserialize($landing_page['params']);
          $request->addRequestParameters($params);
          // $request->setParameter('landing', $result['matches'][0]['id']);
          $request->setParameter('landing_seo_text', $landing_page['seo_text']);
          //Шаблон альтернативных заголовков для лотов
          $request->setParameter('landing_lot_title_prefix', (!empty($landing_page['lot_title_prefix']) ? trim($landing_page['lot_title_prefix']) : ''));
          $this->getResponse()->setCookie('landing_hash', DomusSearchRoute::buildHashFromParams($params));

          //Выставляем юзеру текущий регион
          if(!empty($params['region_id'])) {
            $this->getUser()->current_region = Doctrine::getTable('Region')->find( $params['region_id'] );
            $this->getResponse()->setCookie('current_region', $params['region_id']);
          }
        }
      }
      //Если запрос к "посадочной" странице
      elseif($request->hasParameter('slug') && !$request->isXmlHttpRequest()) {
        $options = array(
          'limit'       => 1,
          'maxmatches'  => 1,
          'offset'      => 0
        );

        $this->sphinx = new DomusSphinxClient($options);
        $this->sphinx->getLandingPages(array(
            'url'       => $request->getParameter('slug'),
            'type'      => Lot::getRealType($request->getParameter('type')),
            'region_id' => $this->getUser()->current_region->id
        ));
        $result = $this->sphinx->getRes();
        if(!empty($result['matches'])) { //Посадочная страница
          $landing_page = $result['matches'][0]['attrs'];
          //Если категория не совпадает — 404
          if(!preg_match('/' . Lot::getRealType($request->getParameter('type')) . '/', $landing_page['params'])) {
            $this->forward404();
          }
          //Выгружаем параметры запроса
          $params = unserialize($landing_page['params']);
          $request->addRequestParameters($params);
          $request->setParameter('landing', $result['matches'][0]['id']);
          $request->setParameter('landing_seo_text', $landing_page['seo_text']);
          //Шаблон альтернативных заголовков для лотов
          $request->setParameter('landing_lot_title_prefix', (!empty($landing_page['lot_title_prefix']) ? trim($landing_page['lot_title_prefix']) : ''));
          $this->getResponse()->setCookie('landing_hash', DomusSearchRoute::buildHashFromParams($params));

          //Выставляем юзеру текущий регион
          if(!empty($params['region_id'])) {
            $this->getUser()->current_region = Doctrine::getTable('Region')->find( $params['region_id'] );
            $this->getResponse()->setCookie('current_region', $params['region_id']);
          }
        }
        else { // apartament-sale/kvartira-taganka
          $params = DomusSearchRoute::extractParts(
            $request->getParameter('slug'),
            Lot::getRealType($request->getParameter('type'))
          );

          $this->sphinx = new DomusSphinxClient($options);
          $this->sphinx->getLandingPages(array(
              'url'       => $request->getParameter('slug'),
              'type'      => Lot::getRealType($request->getParameter('type')),
              'region_id' => $this->getUser()->current_region->id
          ));
          $result = $this->sphinx->getRes();
          if(!empty($result['matches'])) { //Посадочная страница
            $landing_page = $result['matches'][0]['attrs'];
            //Если категория не совпадает — 404
            if(!preg_match('/' . Lot::getRealType($request->getParameter('type')) . '/', $landing_page['params'])) {
              $this->forward404();
            }
            //Выгружаем параметры запроса
            $params = unserialize($landing_page['params']);
            $request->addRequestParameters($params);
            $request->setParameter('landing', $result['matches'][0]['id']);
            $request->setParameter('landing_seo_text', $landing_page['seo_text']);
            //Шаблон альтернативных заголовков для лотов
            $request->setParameter('landing_lot_title_prefix', (!empty($landing_page['lot_title_prefix']) ? trim($landing_page['lot_title_prefix']) : ''));
            //WTF?
            //$this->getResponse()->setCookie('landing_hash', $landing_page['query']);
            $this->getResponse()->setCookie('landing_hash', DomusSearchRoute::buildHashFromParams($params));

            //Выставляем юзеру текущий регион
            if(!empty($params['region_id'])) {
              $this->getUser()->current_region = Doctrine::getTable('Region')->find( $params['region_id'] );
              $this->getResponse()->setCookie('current_region', $params['region_id']);
            }
          }
          else { // apartament-sale/kvartira-taganka
            $params = DomusSearchRoute::extractParts(
              $request->getParameter('slug'),
              Lot::getRealType($request->getParameter('type'))
            );
            $this->forward404If(array() === $params);
            $main_params = array();
            foreach($params as $key=>$value) {
              $main_params[] = str_replace('#', '', DomusSearchRoute::buildHashFromParams(array($key=>$value)));
            }
            $this->main_search_params = json_encode($main_params);
            $params += array(
              "currency" => "RUR",
              "location-type" => "form",
              "map-maximized" => "0",
              "region_id" => Toolkit::getRegionId(),
              "restore_advanced" => "1",
              "sort" => "rating-desc",
            );
            $request->addRequestParameters($params);
            $this->getResponse()->setCookie('landing_hash', DomusSearchRoute::buildHashFromParams($params));
          }
        }
      }
      elseif($request->getCookie('landing_hash')) {
        $this->getResponse()->setCookie('landing_hash', '');
      }

      $nodes = $request->getParameter('regionnode', array());

      //WFT? Это осталось от старого роутинга?
      if (!$request->getCookie('js_on')) {
        $request->setMethod('post');
        $request->setParameter('no_layout', true);
        $request->setParameter('pager_type', 'append');
      }

      $this->results = $this->getController()->getPresentationFor('search', 'get');

      $metas = $this->getResponse()->getMetas();
      if(isset($landing_page)) {
        if(!empty($landing_page['title'])) $this->getResponse()->addMeta('title', $landing_page['title']);
        if(!empty($landing_page['description']))  $this->getResponse()->addMeta('description', $landing_page['description']);
        if(!empty($landing_page['keywords']))  $this->getResponse()->addMeta('keywords', $landing_page['keywords']);
        if(!empty($landing_page['h1'])) {
          $this->getResponse()->addMeta('h1', $landing_page['h1']);
          $this->getResponse()->addMeta('name', $landing_page['h1']);
        }
        $this->getResponse()->addMeta('landing', true);
      }
      else {
        $location = array((string) $this->getUser()->current_region);
        if (count($nodes)) {
          $location = array_merge($location, $nodes);
        }

        $this->getResponse()->addMeta('name', implode(', ', $location) . ' - ' . $metas['name']);
      }

      $request->setParameter('current_type', Lot::getRealType($request->getParameter('type')));
    }
    catch (Exception $e) {
      $this->forward404();
    }
  }

  public function executeGet(sfWebRequest $request) {
    try {
      sfConfig::set('banner', 'search');

      $currency = $request->getParameter('currency', 'RUR');
      if (!in_array($currency, array('RUR', 'USD', 'EUR'))) {
        $currency = 'RUR';
      }
      $this->currency = $currency;
      $request->setParameter('current_type', Lot::getRealType($request->getParameter('type')));

      $params = $request->getParameterHolder()->getAll();
      $params['type'] = Lot::getRealType($params['type']);
      unset($params['restore_custom'], $params['referrer'], $params['hash'],
            $params['module'], $params['action'], $params['page'],
            $params['current_type'], $params['current_url'], $params['zoom'],
            $params['restore_region'], $params['utm_source'],
            $params['utm_medium'], $params['utm_campaign'], $params['curl']);
      $params['region_id'] = $this->getUser()->current_region->id;
      $this->getUser()->current_search = $params;

      if(isset($params['landing'])) {
        $this->landing = $params['landing'];
        unset($params['landing']);
      }

      if($request->isXmlHttpRequest()){
        $params_for_lp = $request->getParameterHolder()->getAll();
        $no_redir = !empty($params_for_lp['no_redir']) ? $params_for_lp['no_redir'] : false;
        $params_for_lp = $this->prepareLandingSearchParams($params_for_lp);
        
        $sphinx = new DomusSphinxClient(array( 'offset' => 0, 'limit' => 1 ));
        $sphinx->getLandingPages(array( 'params' => $params_for_lp ));

        $result = $sphinx->getRes();
        if($result['total'] >= 1 && !$no_redir && $result['matches'][0]['attrs']['url'] != 'root') {
          $this->setTemplate(false);
          return $this->renderText(
            sprintf(
              '<span class="redirect-from-ajax" data-url="%s" style="display: none;"></span>',
              $result['matches'][0]['attrs']['url']
            )
          );
        }
      }
      
      $options = array(
        'limit' => sfConfig::get('app_search_max_per_page'),
        'offset' => ($request->getParameter('page', 1) - 1) * sfConfig::get('app_search_max_per_page')
      );

      $this->sphinx = new DomusSphinxClient($options);
      $this->sphinx->getRegionLotsCount($params);
      if ($result = $this->sphinx->getRes()) {
        if (!empty($result['total_found'])) {
          sfConfig::set('lots_in_region_count', $result['total_found']);
        }
      }

      $this->sphinx = new DomusSphinxClient($options);
      $this->sphinx->search($params);
            
      $this->pager = new DomusSphinxSearchPager('Lot', $options['limit'], $this->sphinx);
      $this->pager->setPage($request->getParameter('page', 1));
      $this->pager->init();
      
      $request->setParameter('current_search_results', $this->pager->getResults());

      //Перелинковка
      $this->landings = array();
      $lat = $lng = false;
      $landing_page = Doctrine::getTable('LandingPage')->find($this->landing);
      if ($landing_page) {
        $current_landings = $this->getUser()->current_landings;
        array_push($current_landings, $landing_page->id);
        $this->getUser()->current_landings = $current_landings;
        $lat = (float) $landing_page->latitude;
        $lng = (float) $landing_page->longitude;
      }
      else {
        $lat = (float) $this->getUser()->current_region->latitude;
        $lng = (float) $this->getUser()->current_region->longitude;
      }

      if ($lat && $lng) {
        $this->sphinx2 = new DomusSphinxClient($options);
        $landings = $this->sphinx2->getGeoLandings(
          $this->getUser()->current_region->id,
          $params['type'],
          array($lat, $lng),
          $this->getUser()->current_landings,
          !$this->getRequest()->hasParameter('slug')
        );
        if (!empty($landings)) {
          $this->landings = $landings;
        }
      }


      $nodes = $request->getParameter('regionnode', array());
      $region_id = $this->getUser()->current_region->id;

      $real_estate_type = null;
      if (($request->getParameter('page') == 1 && $request->getCookie('js_on')) || !$request->hasParameter('page')) {
        if (1 == count($nodes)) {
          list($node_name, $node_socr) = Regionnode::unformatName($nodes[0]);
          $node = Doctrine::getTable('Regionnode')->createQuery()
                  ->andWhere('region_id = ?', $region_id)
                  ->andWhere('name = ?', $node_name)
                  ->andWhere('socr = ?', preg_replace('/\.$/', '', $node_socr))
                  ->fetchOne();
          if ($node) {
            if (in_array(Lot::getRealType($request->getParameter('type')), array('commercial-rent', 'commercial-sale'))) {
              if (!empty($params['field'][45]['orlike'][0])) {
                if (count($params['field'][45]['orlike']) == 1) {
                  $real_estate_type = $params['field']['45']['orlike'][0];
                }
              }
            }
            if (Lot::getRealType($request->getParameter('type')) == 'apartament-sale') {
              if (!empty($params['field'][54]['or'][0])) {
                if (count($params['field'][54]['or']) == 1) {
                  $real_estate_type = $params['field'][54]['or'][0];
                }
              }
            }
            if (Lot::getRealType($request->getParameter('type')) == 'apartament-rent') {
              if (!empty($params['field'][55]['orlike'][0])) {
                if (count($params['field'][55]['orlike']) == 1) {
                  $real_estate_type = $params['field'][55]['orlike'][0];
                }
              }
            }

            $description = $node->getSeoText(Lot::getRealType($request->getParameter('type')), $real_estate_type);
          }
        }
        elseif (0 == count($nodes) && preg_match('/'. Lot::getRealType($request->getParameter('type')) . '$/', $request->getPathInfo())){
          $description = SeoTexts::getSeoTextForRegion(Lot::getRealType($request->getParameter('type')), $region_id);
        }

        //Вывод SEO-текста посадочной страницы
        if($request->hasParameter('landing_seo_text')) {
          $description = $request->getParameter('landing_seo_text');
        }

        if (!empty($description) && (strlen($description) > 10 || $request->hasParameter('landing_seo_text'))) {
          if($real_estate_type == "комната"){
            $description = str_replace(
              array(
                "квартира/","квартиры/","квартиру/",
                "квартир/","Квартира","Квартиру",
                "Квартиры","Квартир"
              ),"",$description
            );
          } else {
            $description = str_replace(
              array(
                "/комната","/комнаты","/комнату",
                "/комнат","Комната","Комнату",
                "Комнаты","Комнат"
              ),"",$description
            );
          }

          $district_description = '<div class="district-description-text">' . $description . '</div>';
        }
      }

      $this->setLayout(false);

      if (!empty($this->landings)) {
        sfContext::getInstance()->getConfiguration()->loadHelpers('Domus');
        $links = format_seo_links($this->landings);
        if ($request->isXmlHttpRequest()) {
          $this->links = $links;
        }
        if (empty($this->links)) {
          $this->getResponse()->setSlot('seo-links', $links);
        }
      }

      //Вывод анонсов статей и новостей, если нет СЕО-текста
      if (empty($district_description)) {
        $options = array('offset' => 0, 'limit' => 12);
        $query = Lot::$type_ru[Lot::getRealType($request->getParameter('type'))];
        $query = preg_replace(array('#продажа#u','#аренда#u', '#недв\.#u','#комер#u'), 
                array('(продажа | купить)','(аренда | снять)','недвижимость','коммер'), $query);
        $query .= empty($params['q_text']) ? '' : ' "' . $params['q_text'] . '"~3';

        $sphinx = new DomusSphinxClient($options);
        $sphinx->SetFilter('main_region_id', array($region_id));
        $sphinx->setMatchMode($sphinx::SPH_MATCH_EXTENDED2);
        $sphinx->SetSortMode($sphinx::SPH_SORT_ATTR_DESC, 'created_at');

        $result = $sphinx->query($query, 'leads_main');
        if(!empty($result['matches'])) {
          $news = array();
          $leads = array();
          foreach ($result['matches'] as $k => $doc) {
            $doc['attrs']['lid'] = (!isset($doc['attrs']['lid'])) ? $doc['attrs']['lead'] : $doc['attrs']['lid']; //tmp
            $leads[$k] = $doc['attrs']['lid'];

            $doc['attrs']['id'] = $doc['id'];
            $curr_news = new News();
            $curr_news->fromArray($doc['attrs']);
            $news[$k] = $curr_news;
          }

          $excerpts = $sphinx->BuildExcerpts($leads, 'leads_main', $query, array(
              'limit' => 0,
              "html_strip_mode" => "retain"
          ));
          foreach ($excerpts as $k => $excerpt) {
            $news[$k]->setLid($excerpt);
          }

          $district_description = $this->getPartial('news/list', array(
              'primary_news' => array(),
              'news' => $news,
              'show_all_link' => false,
              'three_columns' => true,
          ));

          $district_description = '<div class="district-description-text news-three-columns">' . $district_description . '</div>';
        }
      }

      if (!empty($district_description)) {
        if ($request->isXmlHttpRequest()) {
          $this->district_description = $district_description;
        }
        else {
          $this->getResponse()->setSlot('district-description', $district_description);
        }
      }
      $this->sphinx->Close();

      MetaParse::setMetas($this);
      $canonical = $this->getCaconicalLink();
      $this->meta_data = json_encode($this->getResponse()->getMetas());
      $metas = $this->getResponse()->getMetas();
      if(!$this->pager->getNbResults()) {
        $metas['robots'] = 'noindex, nofollow';
      }
      $this->meta_data = json_encode($metas);
    }
    catch (Exception $e) {
      $this->forward404();
    }
  }
  
  private function prepareLandingSearchParams($params_for_lp = null){
    $allowed_keys = array(
        'q',
        'region_id',
        'currency',
        'map-maximized', 
        'restore_advanced',
        'type',
        'location-type',
        'regionnode',
        'price',
        'field',
        'q_text',
        'q_text_enabled',
        'sort',
        'no_layout',
        'square'
    );
    
    if(empty($params_for_lp)) 
      $params_for_lp = $this->getRequest()->getParameterHolder()->getAll();
    
    foreach (array_keys($params_for_lp) as $k) {
      if(!in_array($k, $allowed_keys)) unset( $params_for_lp[$k] );
    }
    
    $params_for_lp['map-maximized'] = !empty($params_for_lp['map_maximized'])
      ? $params_for_lp['map_maximized'] : '0';

    //no_layout случай
    if(!empty($params_for_lp['no_layout'])){
      $params_for_lp['currency'] = 'RUR';
      $params_for_lp['location-type'] = 'form';
      $params_for_lp['map-maximized'] = '0';

      unset($params_for_lp['no_layout']);
    }
    //Добавка к поиску по улице
    if(!empty($params_for_lp['q_text'])){
      $params_for_lp['q'] = $params_for_lp['q_text'];
      $params_for_lp['q_text_enabled'] = 1;
    }
    //Доп. сортировка нодов
    if(!empty($params_for_lp['regionnode'])){
      sort($params_for_lp['regionnode']);
    }
    //Сортировка произвольных полей
    if(!empty($params_for_lp['field'])){
      ksort($params_for_lp['field']);
    }
    //Статичные параметры
    $params_for_lp['restore_advanced'] = "1";
    $params_for_lp['sort'] = "rating-desc";
    
    //Дополнение базовыми параметрами
    if(!isset($params_for_lp['currency'])) $params_for_lp['currency'] = 'RUR';
    if(!isset($params_for_lp['location-type'])) $params_for_lp['location-type'] = 'form';
    
    return $params_for_lp;
  }
  
  private function getCaconicalLink($set = true){
    $request = $this->getRequest();
    $link = array();

    $params = array(
        'region_id' => $request->getParameter('region_id'),
        'type' => $request->getParameter('type')
    );
    if( $request->hasParameter('regionnode') && count($request->getParameter('regionnode')) == 1 )
      $params['regionnode'] = $request->getParameter('regionnode');
    
    $params = $this->prepareLandingSearchParams($params);
    
    $sphinx = new DomusSphinxClient(array( 'offset' => 0, 'limit' => 1 ));
    if(!empty($params['regionnode'])) {
      $sphinx->getLandingPages(array( 'params' => $params ));
      $result = $sphinx->getRes();
      if(!empty($result['total'])) {
        $link[] = $result['matches'][0]['attrs']['url'];
      }
    }

    //Real type to routing
    array_unshift($link, $request->getParameter('type'));
    if(!in_array($link[0], array_keys(Lot::$_routing_types)))
      $link[0] = array_search ($link[0], Lot::$_routing_types);

    $sphinx->Close();
    
    $link = $this->generateUrl('homepage',array(),true).implode('/', $link);
    if($set)
      $this->getResponse()->setSlot('canonical-link', sprintf('<link rel="canonical" href="%s" />',$link));
    
    return $link;
  }

  public function executeGetlast(sfWebRequest $request) {
    $data = array();
    $options = array(
        'limit' => $request->getParameter('limit', 4),
        'offset' => ($request->getParameter('page', 1) - 1) * $request->getParameter('limit', 4)
    );

    $params = $request->getParameterHolder()->getAll();
    if( !empty($params['params'] )) {
      if(!is_array( $params['params'])) {
        $params['params'] = json_decode($params['params'], true);
        if(json_last_error()) $params['params'] = array();
      }
      $params = array_merge($params, $params['params']);
      unset($params['params']);
    }
    $params['region_id'] = $this->getUser()->current_region->id;
    unset($params['images']);
    $this->sphinx = new DomusSphinxClient($options);
    $this->sphinx->search($params);

    $result = $this->sphinx->getRes();
    if (count($result['matches']) > 0) {
      $pager = new DomusSphinxSearchPager('Lot', $request->getParameter('limit', 4), $this->sphinx);
      $pager->setPage($request->getParameter('page', 1));
      $pager->init();

      $data = array(
          'lots' => array(),
          'total' => $pager->getNbResults(),
          'nb_pages' => $pager->getLastPage(),
      );
      $add = 0;
      $template = 'lot/list-item';
      foreach ($pager->getResults() as $lot) {
        $data['lots'][] = $this->getPartial($template, array('lot' => $lot));
      }
    }

    return $this->renderText(json_encode($data));
  }
  
  public function executeSimilar(sfWebRequest $request) {
    $data = array();
    $options = array(
      'limit' => $request->getParameter('limit', 4),
      'offset' => ($request->getParameter('page', 1) - 1) * $request->getParameter('limit', 4)
    );
    
    $this->lot = Doctrine::getTable('Lot')->find($request->getParameter('id'));
    if ($this->lot) {
      $params = array(
        'type'        => $this->lot->type,
        'region_id'   => $this->lot->region_id,
        'id'          => $this->lot->id,
      );
      if ($request->getParameter('check_geo') == 1) {
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

      if ($request->getParameter('check_price') == 1) {
        $min_price = (substr($this->lot->getLotInfoField(70), 3) * $this->lot->exchange) - 30000;
        $min_price = ($min_price < 0) ? 0 : $min_price;
        $max_price = (substr($this->lot->getLotInfoField(71), 3) * $this->lot->exchange) + 30000;

        $price = array(
          'from'  =>  $min_price,
          'to'    =>  $max_price
        );

        $params['price'] = $price;
      } elseif ($request->getParameter('check_regular_price') == 1){
        $params['price'] = array(
          'from' => $this->lot->getPriceExchanged('RUR') * 0.9,
          'to' => $this->lot->getPriceExchanged('RUR') * 1.1
        );
      }

      $this->sphinx = new DomusSphinxClient($options);
      $this->sphinx->searchSimilar($params);

      $pager = new DomusSphinxSearchPager('Lot', $options['limit'], $this->sphinx);
      $pager->setPage($request->getParameter('page', 1));
      $pager->init();
      
      $data = array(
          'lots' => array(),
          'total' => $pager->getNbResults(),
          'nb_pages' => $pager->getLastPage(),
      );
      $add = 0;
      $template = 'lot/list-item';
      foreach ($pager->getResults() as $lot) {
        $data['lots'][] = $this->getPartial($template, array('lot' => $lot));
      }
      
      return $this->renderText(json_encode($data));
    }
  }

  public function executeNotify(sfWebRequest $request) {
    $this->forward404Unless(empty($this->getUser()->current_search));

    $hash = md5(serialize($this->getUser()->current_search));
    $search = Doctrine::getTable('Search')->findOneByHash($hash);

    $form = new NotificationForm();
    $data = array(
        'model' => 'Search',
        'field' => '',
        'pk' => $search ? $search->id : 'create',
        'email' => $this->getUser()->email
    );
    $form->setDefaults($data);

    if ($request->isMethod('post')) {
      $data['email'] = $request->getParameter('email');
      $data['period'] = $request->getParameter('period');
      $form->bind($data);

      if ($form->isValid()) {
        if ($data['pk'] == 'create') {
          $search = new Search();
          $search->param = serialize($this->getUser()->current_search);
          $search->hash = $hash;
          $search->save();
          $data['pk'] = $search->id;
          $form->bind($data);
        }

        $form->save();
        $msg = 'Вы успешно подписались на обновления';
        if ($request->isXmlHttpRequest()) {
          return $this->renderText(json_encode(array('valid' => true, 'message' => $msg)));
        } else {
          $this->getUser()->setFlash('subscribed_success', $msg);
          $this->redirect('@homepage');
        }
      } else {
        return $this->renderText(json_encode($form->getErrorsArray()));
      }
    }

    $this->form = $form;
  }

  public function executeTmp(sfWebRequest $request) {
    $searches = Doctrine::getTable('Search')->findAll();

    foreach ($searches as $search) {
      echo '<h2>' . $search->hash . '</h2>';
      echo '<p>' . $search->text . '</p>';
    }

    exit;
  }

  public function executeObject(sfWebRequest $request) {
    if(!$request->hasParameter('type') || !$request->hasParameter('region_id')) {
      $this->redirect404();
    }

    $options = array(
      'limit' => 1
    );

    $params = array(
      'type'      => Lot::getRealType($request->getParameter('type')),
      'region_id' => $request->getParameter('region_id'),
    );

    $this->sphinx = new DomusSphinxClient($options);
    $lot = $this->sphinx->searchOneLot($params);
    $this->sphinx->Close();

    if(!empty($lot['matches'][0])) {
      $url = $this->generateUrl('show_lot',
        array(
          'id' => $lot['matches'][0]['id'],
          'type' => Lot::getRealType($request->getParameter('type'))
        )
      );
    }
    else {
      $url = $this->generateUrl('search', $params);
    }

    $this->redirect($url, 307);
  }

  public function executeLanding(sfWebRequest $request) {
    $form = new LandingPageForm();
    $params = $this->getUser()->current_search;
    //no_layout случай
    if(!empty($params['no_layout'])){
      $params['currency'] = 'RUR';
      $params['location-type'] = 'form';
      $params['map-maximized'] = '0';
      
      unset($params['no_layout']);
    }
    //Добавка к поиску по улице
    if(!empty($params['q_text'])){
      $params['q'] = $params['q_text'];
      $params['q_text_enabled'] = 1;
    }
    //Доп. сортировка нодов
    if(!empty($params['regionnode'])){
      sort($params['regionnode']);
    }
    //Сортировка произвольных полей
    if(!empty($params['field'])){
      ksort($params['field']);
    }
    //Статичные параметры
    $params['restore_advanced'] = "1";
    $params['sort'] = "rating-desc";

    $sphinx = new DomusSphinxClient(array( 'offset' => 0, 'limit' => 25 ));
    $sphinx->getLandingPages(array( 'params' => $params ));
    
    $result = $sphinx->getRes();
    $same = array();
    if($result['total']) {
      foreach ($result['matches'] as $lp){
        $same[] = $params['type'] . '/' . $lp['attrs']['url'];
      }
    } 
    $this->same = $same;

    if ($request->isMethod('post') && $request->hasParameter('landing_page')) {
      $data = $request->getParameter('landing_page');
      ksort($params);
      $data['params'] = $params;
      //Регион и тип для валидации
      $data['region_id'] = $data['params']['region_id'];
      $data['type'] = $data['params']['type'];
      
      $form->bind($data);
      
      if ($form->isValid()) {
        $form->save();

        $msg = '"Посадочная" страница успешно создана!';
        if ($request->isXmlHttpRequest()) {
          return $this->renderText(json_encode(array('valid' => true, 'message' => $msg)));
        } else {
          $this->getUser()->setFlash('subscribed_success', $msg);
          $this->redirect('@homepage');
        }
      } else {
        return $this->renderText(json_encode($form->getErrorsArray()));
      }
    }

    $this->form = $form;
  }

  public function executeRslist(sfWebRequest $request)
  {
    $region_ids = array();
    $regions = array();
    $query = '@type ';
    $type = $request->getParameter('type', 'rajon');
    $sphinx = new DomusSphinxClient(array( 'offset'=> 0, 'limit' => 1000000 ));
    
    //Определяем регион
    if(sfConfig::get('is_new_building')) {
      $region_ids = array(77,50);
      $regions = Doctrine::getTable('Region')->findByDql('id = ? OR id = ?', array(77,50));
      $query .= $sphinx->EscapeString('new_building');
    }elseif(sfConfig::get('is_cottage')) {
      $region_ids[] = 50;
      $regions[] = Doctrine::getTable('Region')->find(50);
      $query .= 'cottage';
    }else{
      $region_ids[] = $this->getUser()->current_region->id;
      $regions[] = $this->getUser()->current_region;
      $query .= 'apartament | house | commercial';
    }

    $query .= " @params =regionnode -(field) -(q_text) @url $type$";
        
    //Получаем ноды
    $sphinx->setFilter('region_id', $region_ids);
    $sphinx->setMatchMode(DomusSphinxClient::SPH_MATCH_EXTENDED2);
    $sphinx->SetGroupBy('hash', DomusSphinxClient::SPH_GROUPBY_ATTR, 'url ASC');
    $result = $sphinx->query($query, 'landing_pages');
    if (empty($result['total_found'])) $this->forward404();
    
    $nodes = array();
    foreach ($result['matches'] as $node) {
      if(empty($nodes[$node['attrs']['region_id']]['desc'])){
        foreach ($regions as $region) 
          if($region->id == $node['attrs']['region_id']) break;
          
        $nodes[$node['attrs']['region_id']]['region'] = $region->toArray();
        $nodes[$node['attrs']['region_id']]['region']['fullname'] = $region->getFullNamePrepositional();
      }
      $nodes[$node['attrs']['region_id']]['nodes'][$node['attrs']['type']][] = $node;
    }
    krsort($nodes);
    
    $sphinx->Close();
    unset($result, $regions);
    
    $this->type = $type;
    $this->data = $nodes;
  }
}
