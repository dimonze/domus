<?php

class UpdateOldUserPhotoTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
    ));

    $this->namespace        = 'domus';
    $this->name             = 'UpdateOldUserPhoto';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [UpdateOldUserPhoto|INFO] task does things.
Call it with:

  [php symfony UpdateOldUserPhoto|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();

    $client = sfGearmanProxy::getClient();
    $client->setCompleteCallback(array($this, 'getPhoto'));

    ini_set('memory_limit', '1024M');
    ini_set('max_execution_time', 0);

    $users = $conn->prepare('
      SELECT id FROM user
      WHERE (deleted = ? OR deleted IS NULL)
        AND (inactive = ? OR inactive IS NULL)
        AND (photo = ? OR photo IS NULL)'
    );
    $users->execute(array(0, 0, ''));
    $i = 0;
    while ($user = $users->fetchColumn()) {
      $client->addTask($client->getMethodName('update_user_photo'), $user);
      if ($i % 5000 == 0) {
        $client->runTasks();
      }
      $i++;
    }
    $client->runTasks();
    $this->logSection('users', 'Count users: ' . $i);
  }

  public function getPhoto($task)
  {
    $data = unserialize($task->data());
    if (count($data) > 1) {
      $this->logSection(
        'success',
        'User #' . $data[0] . ', Photo: ' . $data[1] .
        'Full path: ' . $data[2]
      );
    }
    else {
      $this->logSection('error', 'User #' . $data[0], null, 'ERROR');
    }
  }
}
