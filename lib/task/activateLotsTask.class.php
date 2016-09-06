<?php

class activateLotsTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
    ));

    $this->namespace        = 'domus';
    $this->name             = 'activateLots';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [activateLots|INFO] task does things.
Call it with:

  [php symfony activateLots|INFO]
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

    $client = sfGearmanProxy::getClient();
    $client->setCompleteCallback(array($this, 'getLot'));

    $lots = $conn->prepare(
      'SELECT l.id FROM lot l
       LEFT JOIN user u ON u.id = l.user_id
       WHERE l.created_at > ?
         AND l.status = ?
         AND (l.deleted_at = ? OR l.deleted_at IS NULL)
         AND (u.deleted_at = ? OR u.deleted_at IS NULL)
         AND (u.inactive = ? OR u.inactive IS NULL)
         AND u.type = ?
       ORDER BY l.active_till ASC'
    );
    $lots->execute(array('2011-09-01 00:00:00', 'inactive', 0, 0, 0, 'source'));
    $i = 0;
    $inner_counter = 0;
    $active_till_day = 0;


    while ($id = $lots->fetchColumn()) {
      $inner_counter++;
      $this->logSection('lot', 'Activate Lot #' . $id);

      $workload = array(
        'id'              =>  $id,
        'active_till_day' =>  $active_till_day
      );

      $client->addTask($client->getMethodName('activate_lot'), serialize($workload));

      if($inner_counter == 500) {
        $this->logSection('task', 'Sleep 10 minutes', null, 'ERROR');
        $this->logSection('gearman', 'Run Tasks', null, 'ERROR');
        $client->runTasks();
        sleep(100);
        $active_till_day++;
        $inner_counter = 0;
      }
      $i++;
    }

    $this->logSection('gearman', 'Run Tasks', null, 'ERROR');
    $client->runTasks();

    $this->logSection('result', $i . ' Lots activated');

    $end_mic = microtime(true) - $start_mic;
    $this->logSection('task', 'Task work ' . $end_mic . ' sec.', null, 'ERROR');
  }

  public function getLot($task)
  {
    if (null != $task->data()) {
      $lot_info = unserialize($task->data());
      if (empty($lot_info['active_till']) || empty($lot_info['status'])) {
        $this->logSection('result', 'Lot #'. $lot_info['id'] . ' not activated' , null, 'ERROR');
      }
      else {
        $this->logSection('result', 'Lot #'. $lot_info['id'] . ' Status: ' . $lot_info['status'] . ' Active Till: ' . $lot_info['active_till']);
      }
    }
    else {
      $this->logSection('result', 'No Lot Info');
    }
  }
}