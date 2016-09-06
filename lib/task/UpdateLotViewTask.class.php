<?php

class UpdateLotViewTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      // add your own options here
    ));

    $this->namespace        = 'domus';
    $this->name             = 'UpdateLotView';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [UpdateLotView|INFO] task does things.
Call it with:

  [php symfony UpdateLotView|INFO]
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

    $lots = $conn->prepare('
      SELECT id
      FROM lot_view');
    $lots->execute();

    while($lot_id = $lots->fetchColumn()) {
      $lot_view = Doctrine::getTable('LotView')->find($lot_id);
      $lot_view->lot_type = $lot_view->Lot->type;
      $lot_view->save();
      $this->logSection('lot_view', 'Saved lot_view #' . $lot_id);
    }
  }
}
