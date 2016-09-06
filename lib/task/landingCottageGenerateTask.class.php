<?php

class landingCottageGenerateTask extends sfBaseTask
{
  protected $created = 0;
  protected $errors = array();
  
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
    $this->name             = 'cottage-generate';
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
    
    $start = microtime(true);

    $templates = array(
        'all' => array(
            'title' => 'Коттеджные поселки [по шоссе / район], участки [по шоссе / район], таунхаусы [по шоссе / район], купить коттедж, дом и участок в коттеджном поселке, цены и отзывы',
            'description' => 'Коттеджные поселки [по шоссе / район], участки [по шоссе / район], таунхаусы [по шоссе / район], купить коттедж, дом и участок в коттеджном поселке, цены и отзывы',
            'keywords' => 'Коттеджные поселки [по шоссе / район], участки [по шоссе / район], таунхаусы [по шоссе / район], купить коттедж, дом, участок в коттеджном поселке, цены и отзывы',
            'h1' => 'Коттеджные поселки, таунхаусы и участки [по шоссе / район]'
        ),
        'poselki' => array(
            'title' => 'Коттеджные поселки [по шоссе / район], купить коттедж в коттеджном поселке [по шоссе / район], купить дом в поселке без посредников, цены и отзывы о поселках',
            'description' => 'Коттеджные поселки [по шоссе / район], купить коттедж в коттеджном поселке [по шоссе / район], купить дом в поселке без посредников, цены и отзывы о поселках',
            'keywords' => 'Коттеджные поселки [по шоссе / район], купить коттедж в коттеджном поселке [по шоссе / район], купить дом в поселке без посредников, цены и отзывы о поселках',
            'h1' => 'Коттеджные поселки [по шоссе / район]'
        ),
        'townhouse' => array(
            'title' => 'Таунхаусы [по шоссе / район], купить таунхаус [по шоссе / район] без посредников, дуплексы [по шоссе / район], цены и отзывы',
            'description' => 'Таунхаусы [по шоссе / район], купить таунхаус [по шоссе / район] без посредников, дуплексы [по шоссе / район], цены и отзывы',
            'keywords' => 'Таунхаусы [по шоссе / район], купить таунхаус [по шоссе / район] без посредников, дуплексы [по шоссе / район], цены и отзывы',
            'h1' => 'Таунхаусы и дуплексы [по шоссе / район]'
        ),
        'uchastki' => array(
            'title' => 'Участки [по шоссе / район], участки в коттеджных поселках [по шоссе / район] без подряда, купить участок в поселке [по шоссе / район] без посредников, цены и отзывы',
            'description' => 'Участки [по шоссе / район], участки в коттеджных поселках [по шоссе / район] без подряда, купить участок в поселке [по шоссе / район] без посредников, цены и отзывы',
            'keywords' => 'Участки [по шоссе / район], участки в коттеджных поселках [по шоссе / район] без подряда, купить участок в поселке [по шоссе / район] без посредников, цены и отзывы',
            'h1' => 'Участки в коттеджных поселках [по шоссе / район]'
        )
    );
    //Towns
    $rnodes = $conn->prepare('
      SELECT name, socr, region_id
      FROM regionnode 
      WHERE region_id = ?
        AND (socr = ? OR socr = ?)'
    );
    $rnodes->execute(array( 50, 'район','р-н' ));
    while ($node = $rnodes->fetch()) {
      foreach ($templates as $type => $tpl) {
        $this->createLandingPage($node, $tpl, $type);
      }
    }
    
    //Shosse
    $rnodes = $conn->prepare('
      SELECT name, socr, region_id
      FROM regionnode 
      WHERE region_id = ?
        AND socr = ?'
    );
    $rnodes->execute(array( 50, 'ш' ));
    while ($node = $rnodes->fetch()) {
      foreach ($templates as $type => $tpl) {
        $this->createLandingPage($node, $tpl, $type);
      }
    }
    
    return $this->complete($start);
  }
  
  protected function complete($start)
  {
    if(!empty($this->errors))
      $this->logSection('general', count($this->errors)." errors occurred." . ( count($this->errors) ? ' Error ID\'s: '.implode(', ',$this->errors) : '' ));
    
    $this->logSection('general', "$this->created landing pages were created over ". round(microtime(true)-$start,2) .' second(s)');
    $this->logSection('general', "Peak memory usage: " . round(memory_get_peak_usage() / (1024 * 1024)) . 'MB');
  }
  
  protected function createLandingPage($node, $seo, $type)
  {
    $params = array();
    $replacment = $node['name'];
    $slug = array();
    
    if($type != 'all'){ 
      array_push($slug, $type);
      
      switch($type){
        case 'poselki':
          $params['field'] = array( 107 => array( 'or' => array( 'Дом/Коттедж' ) ) );
          break;
        case 'townhouse':
          $params['field'] = array( 107 => array( 'or' => array( 'Таунхаусы и Дуплексы' ) ) );
          break;
        case 'uchastki':
          $params['field'] = array( 107 => array( 'or' => array( 'Участок' ) ) );
          break;
      }
    }
    
    array_push($slug, $node['name']);
    if($node['socr'] == 'ш') {
      array_push($slug, 'shosse');
      $replacment = 'по '. WordInflector::get($node['name'], WordInflector::TYPE_DATIVE) .' шоссе';
    } else {
      array_push($slug, 'rajon');
      $replacment .= ' район';
    }
    $slug = implode('-', array_map(array('Toolkit', 'slugify'), $slug));
    
    $params = array_merge($params, array(
      'type'             => 'cottage-sale',
      'region_id'        => (string) $node['region_id'], 
      'regionnode'       => array( Regionnode::formatName($node['name'], $node['socr']) )
    ));
    $params = $this->prepareLandingSearchParams($params);
    
    //Check dublicates
    $sphinx = new DomusSphinxClient(array( 'offset' => 0, 'limit' => 1 ));
    $sphinx->getLandingPages(array( 'params' => $params ));
    $result = $sphinx->getRes();
    if(!empty($result['total'])) {
      $this->errors[] = $result['matches'][0]['id'];
      return false;
    }
    
    $page = new LandingPage();
    $page->url          = $slug;
    $page->seo_text     = null;
    $page->params       = $params;
    $page->query        = LandingPageBackendForm::generateHash($params);
    $page->region_id    = $node['region_id'];
    $page->type         = 'cottage-sale';
    
    foreach ($seo as $mt => $mtf) {
      $page->$mt = preg_replace('#\[по шоссе / район\]#', $replacment, $mtf);
    }
    
    $page->save();
    $this->logSection('general', "Landing page (ID: {$page->id}) was successfully created");
    $this->created++;
    $page->free();
  }
  
  protected function prepareLandingSearchParams($params_for_lp = null){
    if(empty($params_for_lp)) return false;
    
    $allowed_keys = array(
        'q',
        'region_id',
        'currency',
        'restore_advanced',
        'type',
        'location-type',
        'regionnode',
        'price',
        'field',
        'q_text',
        'q_text_enabled',
        'sort',
        'no_layout'
    );
    
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
    
    ksort($params_for_lp);
    return $params_for_lp;
  }
}
