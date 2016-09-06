<?php

class landingUpdateCoordsTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('onlyempty', null, sfCommandOption::PARAMETER_OPTIONAL, 'Only pages with NULL latitude and longitude. Default — true', true),
    ));
    
    $this->namespace        = 'landing';
    $this->name             = 'update-coords';
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
    $count = 0;
    $sphinx = new DomusSphinxClient(array( 'offset' => 0, 'limit' => 1 ));
    $sphinx->setMatchMode(DomusSphinxClient::SPH_MATCH_EXTENDED);

    $this->logSection('general', 'Start task');
    $regions = $conn->prepare("SELECT DISTINCT `id`, `name` FROM `region`");
    $regions->execute(array());
    //Перебираем регионы
    while ($region = $regions->fetch(Doctrine_Core::FETCH_ASSOC)) {
      $this->logSection('general', "Start processing {$region['id']} region");
      $sphinx->SetFilter( 'region_id', array( intval($region['id']) ) );
      if($options['onlyempty']){
        $sphinx->SetFilterFloatRange( 'latitude', 0, 0 );
        $sphinx->SetFilterFloatRange( 'longitude', 0, 0 );
      }
      //Перебираем посадочные по типам
      foreach (array_keys(Lot::$types) as $type) {
        $pages = $sphinx->Query("@type {$sphinx->EscapeString($type)}", 'landing_pages');
        if( !empty($pages['total']) ) {
          if(count($pages['matches']) < $pages['total']) { //Get all!
            $sphinx->SetLimits(0, intval($pages['total'])); 
            $pages = $sphinx->Query("@type {$sphinx->EscapeString($type)}", 'landing_pages');
          }
          //Обновляем
          foreach ($pages['matches'] as $page) {
            if(empty($page['attrs']['landing_id'])) continue;
            $page = Doctrine::getTable('LandingPage')->find( $page['attrs']['landing_id'] );
            if($page) {
              $page->save();
              $page_id = $page->id;
              $count++;
              $this->logSection('landing page', "Coordinates for $page_id was updated successfully");
            } else
              $this->logSection('landing page', "Landing page $page_id not found");
            $page->free(true);
          }
        }
        $sphinx->SetLimits(0, 1);
      }
      $sphinx->ResetFilters();
      $this->logSection('general', "End processing {$region['id']} region");
    }
    $sphinx->Close();
    
    $this->logSection('general', 'End task');
    $this->logSection('general', "$count landing pages were updated");
    $this->logSection('general', "Execution time: " . round(microtime(true)-$start,5) . ' second(s)');
    $this->logSection('general', "Peak memory usage: " . round(memory_get_peak_usage() / (1024 * 1024)) . 'MB');
  }
}
