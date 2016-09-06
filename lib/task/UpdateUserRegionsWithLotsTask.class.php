<?php

class UpdateUserRegionsWithLotsTask extends sfBaseTask
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
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      // add your own options here
    ));

    $this->namespace        = 'domus';
    $this->name             = 'updateUserRegionsWithLots';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [UpdateUserRegionsWithLots|INFO] task does things.
Call it with:

  [php symfony UpdateUserRegionsWithLots|INFO]
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
    $client->setCompleteCallback(array($this, 'getUserUpdateLog'));

    // Получаем id всех пользователей со слэйва
    $users = $conn->prepare(
      'SELECT id
       FROM user
       WHERE (deleted IS NULL OR deleted = ?)
         AND (inactive IS NULL OR inactive = ?)
      '
    );
    $users->execute(array(0, 0));

    $count = 0;
    while($user_id = $users->fetchColumn()) {
      $this->logSection('user', 'User #' . $user_id);
      //выкидываем всю обработку в воркера, пусть упражняется
      $client->addTask($client->getMethodName('update_user_regions'), $user_id);

      $count++;
      if ($count % 5000 == 0) {
        $client->runTasks();
      }
    }
    $client->runTasks();

    $this->logSection('time', 'Time work: ' . (microtime(true) - $start_mic) . ' sec');
  }

  public function getUserUpdateLog($task)
  {
    if (null != $task->data()) {
      $logs = unserialize($task->data());
      if (is_array($logs)){
        foreach ($logs as $log) {
          $this->logSection('worker', $log);
        }
      }
    }
  }
}
