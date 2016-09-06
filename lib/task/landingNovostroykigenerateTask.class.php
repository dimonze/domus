<?php

class landingNovostroykigenerateTask extends sfBaseTask
{
  protected function configure()
  {
    ini_set('memory_limit', '4G');
    ini_set('max_execution_time', 0);
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
    ));
    
    $this->namespace        = 'landing';
    $this->name             = 'novostroyki-generate';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [landing:novostroyki-generate|INFO] task does things.
Call it with:

  [php symfony landing:novostroyki-generate|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();

    $templates = array();
    
    $templates[77]['title']       = 'Купить квартиру в новостройке у метро metro от застройщика, продажа новостроек Москвы';
    $templates[77]['description'] = 'Место поиска недвижимости Москва – каталог новостроек у метро metro, купить квартиру в новостройке.';
    $templates[77]['keywords']    = 'новостройки metro, купить квартиру в новостройке у метро metro';
    $templates[77]['h1']          = 'Продажа квартир в новостройках у метро metro';
    
    $templates[50]['title']       = 'Купить новостройку в city-p от застройщика, продажа квартир в новостройках Подмосковья';
    $templates[50]['description'] = 'Место поиска недвижимости Московская область – каталог новостроек city-g, купить квартиру в новостройке.';
    $templates[50]['keywords']    = 'новостройки city-g, купить квартиру в новостройке в city-p';
    $templates[50]['h1']          = 'Продажа квартир в новостройках в city-p';
    
    $rnodes = $conn->prepare('
      SELECT name, socr, region_id
      FROM regionnode 
      WHERE region_id = ?
        AND socr = ?'
    );
    
    $rnodes->execute(array(77, 'м'));
    while ($node = $rnodes->fetch()) {
      $this->createLandingPage($node, $templates[77]);
    }
    
    $rnodes = $conn->prepare('
      SELECT name, socr, region_id
      FROM regionnode 
      WHERE region_id = ?
        AND socr = ?'
    );
    
    $rnodes->execute(array(50, 'г'));
    while ($node = $rnodes->fetch()) {
      $this->createLandingPage($node, $templates[50]);
    }
    
    $this->createMoscowLandingPage();
    $this->createPodmoskovieLandingPage();
    
  }
  
  protected function createMoscowLandingPage()
  {
    $seo['title']             = 'Новостройки Москвы от застройщика, купить квартиру в новостройке в Москве, продажа новостроек';
    $seo['description']   = 'Место поиска недвижимости Москва – каталог новостроек в Москве, купить квартиру в новостройке.';
    $seo['keywords'] = 'новостройки Москвы, купить квартиру в новостройке';
    $seo['h1']       = 'Продажа квартир в новостройках Москвы';
    
    //Preparing params
    $params = array(
      'currency'         => 'RUR',
      'restore_advanced' => '1',
      'sort'             => 'rating-desc',
      'location-type'    => 'form',
      'map-maximized'    => '0',
      'region_id'        => '77',
      'type'             => 'new_building-sale',
    );
    
    $url = '';
    //create
    $page = new LandingPage();
    $page->url          = $url;
    $page->seo_text     = null;
    $page->params       = $params;
    $page->query        = LandingPageBackendForm::generateHash($params);
    $page->region_id    = 77;
    $page->type         = 'new_building-sale';
    
    $page->title        = $seo['title'];
    $page->description  = $seo['description'];
    $page->keywords     = $seo['keywords'];
    $page->h1           = $seo['h1'];
    
    $page->save();
  }
  
  protected function createPodmoskovieLandingPage()
  {
    $seo['title']       = 'Новостройки в Подмосковье от застройщика, купить квартиру в новостройке в Московской области, продажа новостроек';
    $seo['description'] = 'Место поиска недвижимости Московская область – каталог новостроек Московской области, купить квартиру в новостройке Подмосковья.';
    $seo['keywords']    = 'новостройки Подмосковья, купить квартиру в новостройке в Московской области';
    $seo['h1']          = 'Продажа квартир в новостройках Подмосковья';
    
    //Preparing params
    $params = array(
      'currency'         => 'RUR',
      'restore_advanced' => '1',
      'sort'             => 'rating-desc',
      'location-type'    => 'form',
      'map-maximized'    => '0',
      'region_id'        => '50',
      'type'             => 'new_building-sale',
    );
    
    $url = '';
    //create
    $page = new LandingPage();
    $page->url          = $url;
    $page->seo_text     = null;
    $page->params       = $params;
    $page->query        = LandingPageBackendForm::generateHash($params);
    $page->region_id    = 50;
    $page->type         = 'new_building-sale';
    
    $page->title        = $seo['title'];
    $page->description  = $seo['description'];
    $page->keywords     = $seo['keywords'];
    $page->h1           = $seo['h1'];
    
    $page->save();
  }


  protected function createLandingPage($node, $seo)
  {
//    if ($node['region_id'] == 77) {
    $pieces = array();
    if('м' == $node['socr']) {
      array_push($pieces, 'metro');
    }
    array_push($pieces, $node['name']);
    if ('р-н' == $node['socr']) {
      array_push($pieces, 'rajon');
    }
//    }
//    else {
//      $pieces = array('Подмосковье', $node['name']);
//    }
    //Preparing params
    $params = array(
      'currency'         => 'RUR',
      'restore_advanced' => '1',
      'sort'             => 'rating-desc',
      'location-type'    => 'form',
      'map-maximized'    => '0',
      'region_id'        => (string) $node['region_id'],
      'type'             => 'new_building-sale',
    );
    $params['regionnode'][] = Regionnode::formatName($node['name'], $node['socr']);
    
    $url = implode('-', array_map(array('Toolkit', 'slugify'), $pieces));
    //create
    $page = new LandingPage();
    $page->url          = $url;
    $page->seo_text     = null;
    $page->params       = $params;
    $page->query        = LandingPageBackendForm::generateHash($params);
    $page->region_id    = $node['region_id'];
    $page->type         = 'new_building-sale';
    
    if ($node['region_id'] == 77) {
      $page->title        = preg_replace('/metro/', $node['name'], $seo['title']);
      $page->description  = preg_replace('/metro/', $node['name'], $seo['description']);
      $page->keywords     = preg_replace('/metro/', $node['name'], $seo['keywords']);
      $page->h1           = preg_replace('/metro/', $node['name'], $seo['h1']);
    }
    else {
      $page->title        = preg_replace('/city-p/', WordInflector::get($node['name'], WordInflector::TYPE_PREPOSITIONAL), $seo['title']);
      $page->title        = preg_replace('/city-g/', WordInflector::get($node['name'], WordInflector::TYPE_GENITIVE), $page->title);
      $page->description  = preg_replace('/city-p/', WordInflector::get($node['name'], WordInflector::TYPE_PREPOSITIONAL), $seo['description']);
      $page->description  = preg_replace('/city-g/', WordInflector::get($node['name'], WordInflector::TYPE_GENITIVE), $page->description);
      $page->keywords     = preg_replace('/city-p/', WordInflector::get($node['name'], WordInflector::TYPE_PREPOSITIONAL), $seo['keywords']);
      $page->keywords     = preg_replace('/city-g/', WordInflector::get($node['name'], WordInflector::TYPE_GENITIVE), $page->keywords);
      $page->h1           = preg_replace('/city-p/', WordInflector::get($node['name'], WordInflector::TYPE_PREPOSITIONAL), $seo['h1']);
      $page->h1           = preg_replace('/city-g/', WordInflector::get($node['name'], WordInflector::TYPE_GENITIVE), $page->h1);
    }
    
    $page->save();
    $page->free();
  }
}
