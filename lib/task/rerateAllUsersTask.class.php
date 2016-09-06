<?php

class rerateAllUsersTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
    ));

    $this->namespace        = 'rating';
    $this->name             = 'rerateAllUsers';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [rerateAllUsers|INFO] task does things.
Call it with:

  [php symfony rerateAllUsers|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    ini_set('memory_limit', '1024M');
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();
    $users = $conn->prepare('
      SELECT id
      FROM user
      WHERE (deleted IS NULL OR deleted = ?)
      AND (inactive IS NULL OR inactive = ?)
      AND (type IN (?, ?, ?))
      ORDER BY rating'
    );
    $users->execute(array(0,0,'company','employee','realtor'));

    $client = sfGearmanProxy::getClient();
    $client->setCompleteCallback(array($this, 'getUserRating'));

    $counter = 0;
    while ($user = $users->fetchColumn()) {
      $this->logSection('user', 'User #' . $user);
      $client->addTask($client->getMethodName('rerate_user_profile'), serialize($user));
      $counter++;

      if ($counter % 100 == 0) {
        $this->logSection('gearman', 'Start jobs!');
        $client->runTasks();
      }
    }
    $this->logSection('gearman', 'Start jobs!');
    $client->runTasks();
    $this->logSection('task', 'COMPLETE');
  }

  public function getUserRating($task)
  {
    if (null != $task->data()) {
      $user = unserialize($task->data());
      if (count($user) > 0) {
        $this->logSection('result', 'User #' . $user['user_id'] . ' with rating = ' . $user['rating']);
      }
      else {
        $this->logSection('result', $user, null, 'ERROR');
      }
    }
  }
}
