<?php

class PrepareLotsRentTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
    ));

    $this->namespace        = 'domus';
    $this->name             = 'PrepareLotsRent';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [PrepareLotsRent|INFO] task does things.
Call it with:

  [php symfony PrepareLotsRent|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();

    $client = sfGearmanProxy::getClient();
    $client->setCompleteCallback(array($this, 'getLotInfo'));

    ini_set('memory_limit', '1024M');
    ini_set('max_execution_time', 0);

    $lots = $conn->prepare('
      SELECT l.id FROM lot l
      LEFT JOIN user u ON u.id = l.user_id
      WHERE l.status = ?
        AND (l.deleted_at = ? OR l.deleted_at IS NULL)
        AND (u.deleted_at = ? OR u.deleted_at IS NULL)
        AND (u.inactive = ? OR u.inactive IS NULL)
        AND (l.type IN(?, ?))
      ');
    $lots->execute(array('active', 0, 0, 0, 'apartament-rent', 'commercial-rent'));
    $i = 0;
    while ($lot_id = $lots->fetchColumn()) {
      $client->addTask($client->getMethodName('prepare_lots_rent'), $lot_id);
      if ($i % 5000 == 0) {
        $client->runTasks();
      }
      $i++;
    }
    $client->runTasks();
    $this->logSection('lots', 'All Lots: ' . $i);
  }

  public function getLotInfo($task)
  {
    if (null != $task->data()) {
      $data = $task->data();
      $this->logSection('lot', 'Lot #' . $data . ' is UPDATED');
    }
    else {
      $this->logSection('lot', 'Lot not updated', null, 'ERROR');
    }
  }
}
