<?php

class fixAddressInfoShosseTask extends sfBaseTask
{
  private $_update_query,
          $_shosse = array();


  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
    ));

    $this->namespace        = '';
    $this->name             = 'fixAddressInfoShosse';
    $this->briefDescription = '';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->initUpdateQuery();
    $this->initShosseArray();

    $q = Doctrine::getTable('Lot')->createQuery()
            ->select('id, address1, address2, address_info')
            ->andWhere('user_id = ?', fetchDompodberemTask::USER_ID)
            ->andWhere('type = ?', 'cottage-sale')
            ->andWhere('parsed_at IS NOT NULL')
            ->orderBy('id');

    foreach ($q->fetchArray() as $lot) {
      $this->processLot($lot);
    }
  }


  private function processLot(&$lot)
  {
    if (!empty($lot['address_info']['street']) && isset($this->_shosse[$lot['address_info']['street']])) {
      $lot_shosse_name = $lot['address_info']['street'];

      if (!is_array($lot['address_info']['region_node'])) $lot['address_info']['region_node'] = array();
      $lot['address_info']['region_node'][] = $this->_shosse[$lot_shosse_name];
      $lot['address_info']['street'] = null;

      if (mb_strpos($lot['address1'], $lot_shosse_name) === false) {
        $lot['address1'] .= ', ш. '.$lot_shosse_name;
      }

      preg_match('/(?:^|,\s*)([^,]+ шоссе)/', $lot['address2'], $matches);
      if (!empty($matches[1])) {
        $lot['address2'] = trim(preg_replace('/[^,]+ шоссе(?:$|,\s*)/iu', ' ', $lot['address2']));
      }

      $this->_update_query->execute(array(
        $lot['address1'],
        $lot['address2'],
        serialize($lot['address_info']),
        $lot['id'],
      ));
    }
  }

  private function initUpdateQuery()
  {
    new sfDatabaseManager($this->configuration);
    $conn = Doctrine_Manager::connection();

    $this->_update_query = $conn->prepare('UPDATE lot SET address1 = ?, address2 = ?, address_info = ? WHERE id = ?');
  }

  private function initShosseArray()
  {
    $this->_shosse = Doctrine::getTable('Regionnode')
            ->createQuery()
            ->select('id, name')
            ->where('socr = ?', 'ш')
            ->execute()
            ->toKeyValueArray('name', 'id');
  }
}
