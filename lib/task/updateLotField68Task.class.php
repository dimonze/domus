<?php

class updateLotField68Task extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev')
    ));

    $this->namespace        = 'domus';
    $this->name             = 'updateLotField68';
    $this->briefDescription = '';    
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();
    $types = array(
      'apartament-rent' => array(
          'field_id' => 68, 
          'price' => 3000,
          'less' => 'сутки',
          'more' => 'месяц'),
      'commercial-rent' => array(
          'field_id' => 69,
          'price' => 1500,
          'less' => 'месяц',
          'more' => 'год')
    );
    ini_set('memory_limit', '1024M');
    ini_set('max_execution_time', 0);
    foreach($types as $type => $data) {
      $this->logSection('Deleting info of ' . $type . ' lots with value of field ' . $data['field_id'] . ' equals to NULL', '');
      $conn->
        prepare('DELETE FROM `lot_info` WHERE field_id = ? AND value IS NULL')->
        execute(array(
          $data['field_id']
        ));
      $this->logSection('Fetching ' . $type . ' lots without field ' . $data['field_id'] . '...', '');
      $query = $conn->prepare(
       'SELECT 
          lot.id, lot.type, lot.price
        FROM 
          `lot`
        WHERE 
          lot.type = ? 
          AND
          lot.id NOT IN (SELECT lot.id FROM lot LEFT JOIN lot_info ON lot_info.lot_id = lot.id WHERE lot_info.field_id = ?)'
      );
      $query->execute(array(
        $type,
        $data['field_id']
      ));
      $counter = 0;
      $this->logSection('Fixing ' . $type . ' lots...', '');
      while($lot = $query->fetch()) {
        $conn->prepare('INSERT INTO lot_info SET lot_id = ?, field_id = ?, value = ?')->execute(array(
          $lot['id'],
          $data['field_id'],  
          $lot['price'] <= $data['price'] ? $data['less'] : $data['more']
        ));
        $counter++;      
      }
      $this->logSection('Fixed ' . $counter . ' ' . $type . ' lots', '');
      $counter = 0;
    }
        
  }
}
