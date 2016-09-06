<?php

/**
 * Task for mass fake user creation
 *
 * @author kmad
 */
class fillYandexAddressInfoTask extends sfBaseTask {

  public function configure() {
    $this->addOptions(array(
      new sfCommandOption('count', null, sfCommandOption::PARAMETER_REQUIRED, 'Lots count'),
    ));

    $this->namespace        = 'domus';
    $this->name             = 'fillYandexAddressInfo';
    $this->briefDescription = 'Fill address_info array for lots from Yandex';
  }

  public function execute($arguments = array(), $options = array()) {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();
    $helper = new AddressHelper();

    $user = 23639;
    $start_mic = microtime(true);
    $lots = $conn->prepare("SELECT `id` FROM `lot` WHERE `user_id` = ? AND `address_info` IS NULL" . (!empty($options['count']) ? ' LIMIT ' . intval($options['count']) : ''));
    $lots->execute(array( $user ));
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
