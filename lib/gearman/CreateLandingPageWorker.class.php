<?php

class CreateLandingPageWorker extends sfGearmanWorker {
  public
    $name = 'create-landing-page',
    $methods = array(
      'create_landing_page'
    );

  public function doCreateLandingPage (GearmanJob $job)
  {
    $this->startJob();
    $workload = unserialize($job->workload());
    $region       = null !== $workload['region_id']      ? Doctrine::getTable('Region')->find($workload['region_id'])          : null;
    $region_node  = null !== $workload['region_node_id'] ? Doctrine::getTable('Regionnode')->find($workload['region_node_id']) : null;
    $ssd          = null !== $workload['ssd_id']         ? Doctrine::getTable('SitemapSeoData')->find($workload['ssd_id'])     : null;
    $street       = null !== $workload['street_id']      ? Doctrine::getTable('Street')->findBy('id', $workload['street_id'])  : null;
    $commercial_type = $workload['commercial_type'];

    $street = !empty($street[0]) ? $street[0] : null;

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
    }
    elseif (in_array($region_node_id, array(2295,2296))) {
      //Get outta here. There are useless pages.
      return $this->completeJob($job, array('id' => null));
    }
    if(null !== $street) {
      array_push($pieces, $street->full_name);
    }
    if(null !== $commercial_type) {
      array_unshift($pieces, $commercial_type);
    }

    //get seo-text
    $seo_text = null;
    if(null !== $street) {
      $section = array_search($ssd->section, array_keys(Lot::$types));
      $query = Doctrine::getTable('SeoTexts')->createQuery()
        ->andWhere('region_id = ?', $region->id)
        ->andWhere('section   = ?', $section);
      $query = null === $region_node
        ? $query->andWhere('region_node_id is null')
        : $query->andWhere('region_node_id = ?', $region_node->id);
      $seo_text = $query->execute();
      $seo_text = $seo_text[0]->text;
    }

    //create
    $page = new LandingPage();
    $page->seo_text  = $seo_text;
    $page->region_id = $region->id;
    $page->type      = $ssd->section;

    //Preparing params
    $params = array(
      'currency'         => 'RUR',
      'restore_advanced' => '1',
      'sort'             => 'rating-desc',
      'location-type'    => 'form',
      'map-maximized'    => '0',
      'region_id'        => (string)$region->id,
      'type'             => $ssd->section,
    );

    if(null !== $region_node) {
      $params['regionnode'][] = $region_node->full_name;
    }

    if(null !== $street) {
      $params['q'] = $params['q_text'] = $street->full_name;
      $params['q_text_enabled'] = 1;
    }


    $word = strpos($ssd->section, 'commercial') !== false
      ? $commercial_type
      : $ssd->link;

    $rooms_map_replace = array(
      'однокомн'    => '1 комнатная квартира',
      'двухкомн'    => '2-х комнатная квартира',
      'трехкомн'    => '3-х комнатная квартира',
      'четырехкомн' => '4-х комнатная квартира',
      'комнат'      => 'комната',
    );
    
    $house_map_replace = array(
      'дач'         => 'дача',
      'дом'         => 'коттедж/дом',
      'коттедж'     => 'коттедж/дом',
      'часть дома'  => 'часть дома',
      'таунхаус'    => 'таунхаус',
      'особняк'     => 'особняк',
      'участ'       => 'участок'
    );

    if(strpos($ssd->section, 'apartament') !== false) {
      foreach($rooms_map_replace as $from => $to) {
        if(mb_stristr($word, $from)) {
          $key = strpos($ssd->section, '-sale') ? '54' : '55';
          $params['field'][$key]['or'][] = $to;
          array_unshift($pieces, $to);
          break;
        }
      }
    }
    
    if(strpos($ssd->section, 'house') !== false) {
      foreach($house_map_replace as $from => $to) {
        if(mb_stristr($word, $from)) {
          $key = strpos($ssd->section, '-sale') ? '64' : null;
          if (null == $key) continue;
          $params['field'][$key] = $to;
          array_unshift($pieces, $to);
          break;
        }
      }
    }

    if(strpos($ssd->section, 'commercial') !== false && null !== $word) {
      $params['field'][45]['orlike'][] = $word;
    }
    ksort($params);

    $page->params = $params;
    $page->query  = LandingPageBackendForm::generateHash($page->params);//$this->makeLink($page->params);
    
    $url = implode('-', array_map(array('Toolkit', 'slugify'), $pieces));
    $page->url = $url;
    
    //Generate new metas
    $landing_params = array(
      'region_name' => $region->name,
      'type'        => $ssd->section,
      'params'      => $params
    );
    
    $metas = MetaParse::generateLandingMeta($landing_params);
    if (count($metas) > 0) {
      foreach ($metas as $key => $value) {
        $page->$key = $value; 
      }
    } 

    //Preparing SEO-things
//    foreach(array('h1' => 'h1', 'title' => 'title', 'description' => 'link') as $key => $ssd_key) {
//      $replace = $pattern = array();
//      $string = $ssd->$ssd_key;
//      $level = $ssd->level;
//      if(null !== $region_node && $region_node->is_metro) {
//        $level = 'metro';
//      }
//
//      switch ($level) {
//        case 'city':
//        case 'district':
//        case 'village':
//        case 'metro':
//          $replace[] = $region_node->full_name_prepositional;
//          break;
//        case 'street':
//          $replace[]  = $street->full_name;
//          break;
//        case 'region':
//          $replace[] = $region->full_name_prepositional;
//          break;
//      }
//
//      switch ($level) {
//        case 'city':
//          $pattern[] = '/\[город([А-я])*\]/';
//          break;
//        case 'metro':
//          $pattern[] = '/\[улиц([А-я])*\]\s\[город([А-я])*\]/';
//          break;
//        case 'street':
//          $pattern[] = '/\[улиц([А-я])*\]/';
//          $pattern[] = '/\[город([А-я])*\]/';
//          if (empty($region_node)) {
//            $replace[]  = 'в ' . $region->full_name_prepositional;
//          }else {
//            $replace[]  = 'в ' . $region_node->name;
//          }
//          break;
//        case 'village':
//          $pattern[] = '/\[деревне или поселке\]/';
//          break;
//        case 'region':
//          $pattern[] = '/\[регион([А-я])*\]/';
//          break;
//        case 'district':
//          $pattern[] = '/\[район([А-я])*\]/';
//          break;
//      }
//
//      if(null !== $commercial_type) {
//        $string = str_replace('[вид недвижимости]', $commercial_type, $string);
//      }
//      $page->$key =  preg_replace($pattern, $replace, $string);
//    }

    $page->save();
    echo date('Y-m-d H:i:s.') . (array_pop(explode('.', array_shift(explode(' ', microtime()))))) . "\n";
    $id = $page->id;
    $page->free(true);
    return $this->completeJob($job, array('id' => $id));
  }
}

