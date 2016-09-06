<?php

/**
 * Parse street coords via gmap
 *
 * @author kmad
 */
class ParseStreetGeoTask extends sfBaseTask {

  public function configure() {

    $this->namespace        = 'domus';
    $this->name             = 'parseStreetGeo';
    $this->briefDescription = 'Parse street coords via gmap';
    $this->addOptions(array(
      new sfCommandOption('dry-run', '-dr', sfCommandOption::PARAMETER_NONE, 'Dry Run?'),
      new sfCommandOption('from-scratch', '-s', sfCommandOption::PARAMETER_NONE, 'Restart parsing from scratch'),
    ));
  }

  public function execute($arguments = array(), $options = array()) {
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
        foreach ($node->Street as $street) {
          if (($street->latitude !== null) && ($street->longitude !== null) && empty($options['from-scratch'])) {
            continue;
          }
          $address = $region->name . ' ' . $node->name . ' ' . $street->name;
          $geo = $this->getGeolocation($address);
          if (!$geo) {
            $this->logSection('    Street', $address . ': Geocoding failed', 120, 'ERROR');
            $street->latitude = 0;
            $street->longitude = 0;
            $street->save();
            $street->free(true);
            continue;
          }
          $this->logSection('    Street', $address . ' lat: ' . $geo['lat'] . ' lng: ' . $geo['lng'], 120);
          if (!$dry) {
            $street->latitude = $geo['lat'];
            $street->longitude = $geo['lng'];
            $street->save();
            $street->free(true);
          }
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
