<?php

/**
 * Parse street coords via gmap
 *
 * @author kmad
 */
class ParseRegionNodeGeoTask extends sfBaseTask {

  public function configure() {

    $this->namespace        = 'domus';
    $this->name             = 'parseRegionNodeGeo';
    $this->briefDescription = 'Parse regionnode coords via gmap';
    $this->addOptions(array(
      new sfCommandOption('dry-run', '-dr', sfCommandOption::PARAMETER_NONE, 'Dry Run?'),
      new sfCommandOption('from-scratch', '-s', sfCommandOption::PARAMETER_NONE, 'Restart parsing from scratch'),
    ));
  }

  public function execute($arguments = array(), $options = array()) {
    ini_set('max_execution_time', 0);
    ini_set('memory_limit', '4G');
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    
    $dry = $options['dry-run'] ? true : false;
    if ($dry) {
      $this->logSection('Mode', 'Using DRY mode');
    } else {
      $this->logSection('Mode', 'Using REAL mode');
    }

    $regions = Doctrine::getTable('Region')->findAll();
    foreach ($regions as $region) {
      $this->logSection('Region',  $region->name);
      foreach ($region->Regionnode as $node) {
        $this->logSection('  Node', $node->name);
        if (($node->latitude !== null) && ($node->longitude !== null) && empty($options['from-scratch'])) {
          continue;
        }
        $address = $region->name . ' ' . $node->full_name;
        $geo = $this->getGeolocation($address);
        if (!$geo) {
          $this->logSection('    Node', $address . ': Geocoding failed', 120, 'ERROR');
          $node->latitude = 0;
          $node->longitude = 0;
          $node->save();
          $node->free(true);
          continue;
        }
        $this->logSection('    Node', $address . ' lat: ' . $geo['lat'] . ' lng: ' . $geo['lng'], 120);
        if (!$dry) {
          $node->latitude = $geo['lat'];
          $node->longitude = $geo['lng'];
          $node->save();
          $node->free(true);
        }
        $node->free(true);
      }
      $region->free(true);
    }
  }
  
  protected function getGeolocation($address) {
      $context = stream_context_create(array('http' => array(
          'proxy' => 'tcp://188.72.68.74:8192',
          'request_fulluri' => true,
      )));
//      $context = null;//disable proxy
      $response = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) .'&sensor=false&language=ru', false, $context);
      $ret = json_decode($response, true);
      return isset($ret['results'][0]['geometry']['location']) ? $ret['results'][0]['geometry']['location'] : false;
  }

}
?>
