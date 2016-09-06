<?php

class repairBuildingYearForParsedTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'domus';
    $this->name             = 'repairBuildingYear';    
  }

  protected function execute($arguments = array(), $options = array())
  {
    ini_set('max_execution_time', 0);
    $this->logSection('Start repairing', '');
    // initialize the database connection
    
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();
    
    $query = $conn->prepare("SELECT l.id, i.value FROM lot l LEFT JOIN lot_info i ON l.id=i.lot_id 
      WHERE i.value != ''
      AND i.field_id = ?
      AND i.value IS NOT NULL 
      AND l.parsed_at IS NOT NULL
      AND l.status = ?");
    
    $query->execute(array(5, 'active'));
    $total = $query->rowCount();
    $this->logSection('Total rows', $total);

    foreach($query->fetchAll() as $key=>$row){
      preg_match('/\d+/', $row['value'], $out);
      $year = $out[0];
      switch(strlen($year)) {
        case 3: $year = $year . '0';   break;
        case 2: $year = '19' . $year;  break;
        case 1: $year = '200' . $year; break;
      }
  
      if($year < 1861 || $year > (date("Y") + 10)) {
      // TODO: I have no idea. =)
      }
      
      if($year != $row['value']) {
        $conn->
        prepare('UPDATE lot_info SET value = ? WHERE lot_id = ? AND field_id = ?')->
        execute(array($year,$row['id'],5));
      }
      if($total>0){
        echo "Done " . (round($key/$total*100, 1)) . "%      \r";
      }
    }
    $this->logSection('Done', '');
  }
}
