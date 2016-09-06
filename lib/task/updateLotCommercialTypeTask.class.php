<?php

class updateLotCommercialTypeTask extends sfBaseTask
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
    $this->name             = 'updateLotCommercialType';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [updateLotCommercialType|INFO] task does things.
Call it with:

  [php symfony updateLotCommercialType|INFO]
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
    $stmt = $conn->prepare(
      'SELECT id
      FROM lot
      WHERE brief LIKE ? AND type IN (?, ?)');
    $stmt->execute(array('%многотипный%', 'commercial-sale', 'commercial-rent'));
    $i = 0;
    while ($id = $stmt->fetchColumn()) {
      $this->logSection('lot', 'Lot #' . $id . ' update BRIEF field.');
      $lot = Doctrine::getTable('Lot')->find($id);
      if ($lot instanceof Lot){
        $lot->brief = str_replace('многотипный', 'многофункциональный', $lot->brief);
        $lot->save();
        $lot->free();
        $this->logSection('lot', 'Lot #' . $id . ' update BRIEF field SUCCESS.');
      }
      else {
        $this->logSection('lot', 'Lot #' . $id . ' update BRIEF field SUCCESS.', null , 'ERROR');
      }
    }
  }
}
