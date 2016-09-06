<?php

/**
 * Task for mass fake user creation
 *
 * @author kmad
 */
class fixAddressCityRegionTask extends sfBaseTask {

  public function configure() {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
    ));

    $this->namespace        = 'fix';
    $this->name             = 'AddressCityRegion';
    $this->briefDescription = "Removes digits only address_info['city_region']";
  }

  public function execute($arguments = array(), $options = array()) {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();
    
    ini_set('memory_limit', '2048M');
    ini_set('max_execution_time', 0);
    
    $helper = new AddressHelper();
    $start_mic = microtime(true);
    $lots = $conn->prepare("SELECT `id` FROM `lot` WHERE status = ? AND `deleted_at` IS NULL AND `address_info` REGEXP ? ORDER BY `id` DESC");
    $lots->execute(array('active','"city_region";s:[0-9]+:"[0-9]+";'));
    while($lot_id = $lots->fetchColumn()) {
      $this->logSection('test', $lot_id);
      $lot = Doctrine::getTable('Lot')->find($lot_id);
      $address_info = $lot->address_info;
      //Clear 'city_region'
      $waste = $address_info['city_region'];
      $address_info['city_region'] = "";
      //Fix address_info
      $region_node = Doctrine::getTable('RegionNode')->find((int)$waste);
      if( $region_node && $region_node->region_id == $lot->region_id ) {
        $address_info["region_node"][] = $waste;
        $address_info["region_node"] = array_unique($address_info["region_node"]);
        for ($i = 0; $i < count($address_info["region_node"]); $i++) {
          if(in_array($address_info["region_node"][$i], array( 2295,2296 ))) 
            unset($address_info["region_node"][$i]);
        }
      }
      $lot->address_info = $address_info;
      //Remove waste from address1. "city_region" is always at the end
      $lot->address1 = preg_replace("#,\s*$waste\s*$#", '', $lot->address1);
      $lot->slug = '';
      $lot->save();
      $this->logSection('lot', 'Lot ' . $lot_id . ' was saved.');
    }
      
    $end_mic = microtime(true) - $start_mic;
    $this->logSection('task', 'Task work ' . $end_mic . ' sec.', null, 'ERROR');
  }
}
?>
