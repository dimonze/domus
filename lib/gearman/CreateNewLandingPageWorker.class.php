<?php

class CreateNewLandingPageWorker extends sfGearmanWorker {
  public
    $name = 'create-new-landing-page',
    $methods = array(
      'create_landing_page'
    );

  public function doCreateLandingPage (GearmanJob $job)
  {
    $this->startJob();
    $workload = unserialize($job->workload());
    $region       = null !== $workload['region_id']      ? Doctrine::getTable('Region')->find($workload['region_id'])          : null;
    $region_node  = null !== $workload['region_node_id'] ? Doctrine::getTable('Regionnode')->find($workload['region_node_id']) : null;
    $type         = $workload['type'];
    $object_type  = $workload['object_type'];
    $conn = Doctrine_Manager::connection();

    if ('new_building-sale' == $type) {
      return $this->completeJob($job, 'No landing for Novostroyki');
    }

    if ('house-rent' == $type && 'default' != $object_type) {
      return $this->completeJob($job, 'NO house rent object types');
    }

    //create url
    $pieces = array();
    $region_node_id = null !== $region_node ? $region_node->id : null;
    if(!in_array($region_node_id, array(null,2295,2296))) {
      if('м' == $region_node->socr) {
        array_push($pieces, 'metro');
      }
      array_push($pieces, $region_node->name);
      if ('р-н' == $region_node->socr) {
        array_push($pieces, 'rajon');
      }
      if('ш' == $region_node->socr) {
        array_push($pieces, 'shosse');
      }
    }
    elseif (in_array($region_node_id, array(2295,2296))) {
      //Get outta here. There are useless pages.
      return $this->completeJob($job, array('id' => null));
    }

    //get seo-text
    $query = Doctrine::getTable('SeoTexts')->createQuery()
      ->andWhere('region_id = ?', $region->id)
      ->andWhere('real_estate_type = ?', $object_type)
      ->andWhere('section = ?', Lot::$types[$type]);

    if ($region_node) {
      $query->andWhere('region_node_id = ?', $region_node->id);
    }
    else {
      $query->andWhere('region_node_id IS NULL');
    }
    $seo_text = $query->execute();

    //create
    $page = new LandingPage();
    // $page->seo_text  = $seo_text;
    $page->region_id = $region->id;
    $page->type      = $type;

    //Preparing params
    $params = array(
      'currency'         => 'RUR',
      'restore_advanced' => '1',
      'sort'             => 'rating-desc',
      'location-type'    => 'form',
      'map-maximized'    => '0',
      'region_id'        => (string)$region->id,
      'type'             => $type,
    );

    if(null !== $region_node) {
      $params['regionnode'][] = $region_node->full_name;
    }

    if(strpos($type, 'apartament') !== false && $object_type != 'default') {
      $key = strpos($type, '-sale') ? '54' : '55';
      $params['field'][$key]['or'][] = $object_type;
      array_unshift($pieces, $object_type);
    }
    
    if(strpos($type, 'house') !== false && $object_type != 'default') {
      $key = strpos($type, '-sale') ? '64' : null;
      if (null != $key) {
        $params['field'][$key] = $object_type;
        array_unshift($pieces, $object_type);
      }
    }

    if(strpos($type, 'commercial') !== false && $object_type != 'default') {
      $params['field'][45]['orlike'][] = $object_type;
      array_unshift($pieces, $object_type);
    }
    if(!empty($params['field'])) ksort($params['field']);
    ksort($params);
    
    //Поиск дубликата по параметрам
    $query = $conn->prepare('SELECT COUNT(*) FROM `landing_page` WHERE `params` = ?');
    $query->execute(array( serialize($params) ));
    if( intval($query->fetchColumn()) ) 
      return $this->completeJob($job, 'Duplicate');

    $page->params = $params;
    $page->query  = LandingPageBackendForm::generateHash($page->params);
    
    $url = implode('-', array_map(array('Toolkit', 'slugify'), $pieces));
    $page->url = ('' != $url) ? $url : 'root';
    
    //Generate new metas
    $landing_params = array(
      'region_name' => $region->name,
      'type'        => $type,
      'params'      => $params
    );
    
    $metas = MetaParse::generateLandingMeta($landing_params);
    if (count($metas) > 0) {
      foreach ($metas as $key => $value) {
        $page->$key = $value; 
      }
    } 

    // если не найден сео текст в SeoTexts
    if (count($seo_text) > 0) {
      $page->seo_text = $seo_text[0]->text;
    }

    if (null != $page->type) {
      $page->save();
      $id = $page->id;
      $page->free(true);
      return $this->completeJob($job, 'Landing page with ID ' . $id . ' was created');
    }
    else {
      return $this->completeJob($job, 'FUCK'); 
    }
  }
}

