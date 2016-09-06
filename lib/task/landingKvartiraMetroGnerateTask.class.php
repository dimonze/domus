<?php

class landingKvartiraMetroGnerateTask extends sfBaseTask
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
    $this->name             = 'kvartira-metro-generate';
    $this->briefDescription = '';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();
    $start = microtime(true);
    $total_added = 0;

    $this->logSection('general', 'Start task');

    //TODO Remove limits
    $regions = $conn->prepare("SELECT DISTINCT `id`, `name` FROM `region`");
    $regions->execute(array());
    //Перебираем регионы
    while ($region = $regions->fetch(Doctrine_Core::FETCH_ASSOC)) {
      $this->logSection('general', "Start processing {$region['id']} region");
      $nodes = $conn->prepare("SELECT * FROM `regionnode` WHERE `socr` IN ('г','пгт','м','м.') AND `region_id` = ?");
      $nodes->execute(array( $region['id'] ));
      //Перебираем города и метро
      while ($node = $nodes->fetch(Doctrine_Core::FETCH_ASSOC)) {
        $node_name = Regionnode::formatName($node['name'], $node['socr']);
        $this->logSection('region', "Start processing \"{$node_name}\"");
        foreach (array('apartament-sale','apartament-rent') as $type) {
          if($this->createLandingPage($type, $node, $region)) $total_added++;
        }
        $this->logSection('region', "End processing \"{$node_name}\"");
      }
      $nodes->closeCursor();
      $this->logSection('general', "End processing {$region['id']} region");
    }
    
    $this->logSection('general', 'End task');
    $this->logSection('general', "Execution time: " . round(microtime(true)-$start,5) . ' second(s)');
    $this->logSection('general', "Was added $total_added landing pages");
    $this->logSection('general', "Peak memory usage: " . round(memory_get_peak_usage() / (1024 * 1024)) . 'MB');
  }
  
  protected function createLandingPage($type, $node, $region)
  {
    $this->logSection('node', "$type");
    $node_name = Regionnode::formatName($node['name'], $node['socr']);
    //Формируем массив "посадочных" параметров
    $landing_params = array(
      'type'        => $type,
      'region_name' => $region['name'],
      'params'      => array(
          'regionnode' => array($node_name),
          'currency'         => 'RUR',
          'restore_advanced' => '1',
          'sort'             => 'rating-desc',
          'location-type'    => 'form',
          'map-maximized'    => '0',
          'region_id'        => (string) $node['region_id'],
          'type'             => $type,
          'field' => array( 
              54 => array(
                'or' => array(
                    '1 комнатная квартира',
                    '2-х комнатная квартира',
                    '3-х комнатная квартира',
                    '4-х комнатная квартира',
                    '5+-?и комнатная квартира'
                )
              ) 
          )
      )   
    );
    
    if($type == 'apartament-rent') {
      $landing_params['params']['field'][55]['or'] = $landing_params['params']['field'][54]['or'];
      unset( $landing_params['params']['field'][54] );
    } else {
      array_push($landing_params['params']['field'][54]['or'], 'квартира со свободной планировкой');
    }

    //Формируем slug с полным видом типа ноды
    $node_name = preg_replace(
            array( '/^м. /','/м /','/^г. /','/^пгт /'),
            array( 'метро ', 'метро ', 'город ', 'пгт '),
            $node_name
    );
    $url = 'kvartira-'.Toolkit::slugify($node_name);
    //Получаем meta и хэш для текущей страницы
    $metas = MetaParse::generateLandingMeta($landing_params);
    $hash = LandingPageBackendForm::generateHash($landing_params);
 
    $el = $this->isLandingExists($node['region_id'], $type, $hash);
    if(!$el) {
      ksort($landing_params['params']);
      //Создаем страницу
      $page = new LandingPage();
      $page->url          = $url;
      $page->seo_text     = null;
      $page->params       = $landing_params['params'];
      $page->query        = $hash;
      $page->region_id    = $node['region_id'];
      $page->type         = $type;
      $page->title        = $metas['title'];
      $page->description  = $metas['description'];
      $page->keywords     = $metas['keywords'];
      $page->h1           = $metas['h1'];
      
      $page->save();
      $this->logSection('node', 'Landing page ID: ' . $page->id);
      $page->free();

      $this->log('TITLE: ' . $metas['title']);
      $this->log('DESCRIPTION: ' . $metas['description']);
      $this->log('KEYWORDS: ' . $metas['keywords']);
      $this->log('H1: ' . $metas['h1']);
      return true;
    }
      
    $this->logSection('node', "Landing for this params already exists. ID: $el");
    return false;
  }
  
  protected function isLandingExists($region_id, $type, $hash)
  {
    if(empty($hash)){
      throw new Exception('Hash must be defined!');
    }
    
    //Ищем идентичную "посадочную" страницу
    $same = Doctrine_Manager::connection()->prepare("SELECT `id` FROM `landing_page` WHERE `region_id` = ? AND `type` = ? AND `query` = ? LIMIT 1");
    $same->execute(array($region_id, $type, $hash));
    if($same = $same->fetchColumn()) return $same;
    return false;
  }
}
