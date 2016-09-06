<?php

class updateRegionsMenuTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      // add your own options here
    ));

    $this->namespace        = 'domus';
    $this->name             = 'updateRegionsMenu';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [updateRegionsMenu|INFO] task does things.
Call it with:

  [php symfony updateRegionsMenu|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();

    ini_set('memory_limit', '1024M');
    ini_set('max_execution_time', 0);
    $start_mic = microtime(true);
    $stmt = $conn->prepare('SELECT id FROM region');
    $stmt->execute();

    $regions = array();
    while($region_id = $stmt->fetchColumn()){
      $this->logSection('region', 'Select Lots for Region #' . $region_id);
      $lots = Doctrine::getTable('Lot')->createQueryActive()
        ->andWhere('region_id = ?', $region_id)
        ->limit(1)
        ->execute();

      if (count($lots) > 0) {
        $this->logSection('region', 'Updating Region #' . $region_id);
        $region = Doctrine::getTable('Region')->find($region_id);
        $region->in_menu = true;
        $region->save();
        $region->free();
      }
      unset($lots);
    }    
  }
}
