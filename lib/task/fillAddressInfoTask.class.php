<?php

/**
 * Task for mass fake user creation
 *
 * @author kmad
 */
class fillAddressInfoTask extends sfBaseTask {

  public function configure() {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('count', null, sfCommandOption::PARAMETER_REQUIRED, 'Lots count'),
    ));

    $this->namespace        = 'fill';
    $this->name             = 'AddressInfo';
    $this->briefDescription = 'Fill address_info array for lots to Yandex';
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
    $lots = $conn->prepare("SELECT `id` FROM `lot` WHERE `status` = ? AND `address_info` IS NULL and `deleted_at` IS NULL" . (!empty($options['count']) ? ' LIMIT ' . intval($options['count']) : ''));
    $lots->execute(array('active'));
    while($lot_id = $lots->fetchColumn()) {
      $lot = Doctrine::getTable('Lot')->find($lot_id);
      $address = $lot->address1 . ', ' . $lot->address2;
      $lot->address_info = $helper->parseAddress($address);
      $lot->save();
      $this->logSection('lot', 'Lot ' . $lot_id . ' was saved.');
    }
        
    $end_mic = microtime(true) - $start_mic;
    $this->logSection('task', 'Task work ' . $end_mic . ' sec.', null, 'ERROR');
  }
}
?>
