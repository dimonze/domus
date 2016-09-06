<?php

class sinonymizeDescriptionTask extends sfBaseTask
{
  protected $save;

  protected function configure()
  {
    $this->addArgument('type', sfCommandArgument::OPTIONAL, 'id|inner|type|all', 'all');
    $this->addOptions(array(
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('app', 'frontend', sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
      new sfCommandOption('clean', false, sfCommandOption::PARAMETER_NONE, 'Clean the <description>'),
    ));

    $this->namespace        = 'synonymize';
    $this->name             = 'description';
    $this->briefDescription = '';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = $this->createConfiguration($options['app'], 'dev');
    $databaseManager = new sfDatabaseManager($configuration);
    $this->conn = Doctrine::getTable('Lot')->getConnection();

    $client = sfGearmanProxy::getClient();;
    $client->setCompleteCallback(array($this, 'logTask'));

    if (is_numeric($arguments['type'])) {
      $stmt = $this->conn->prepare('select id from lot where id = ?');
      $stmt->execute(array($arguments['type']));
    }
    elseif ('inner' == $arguments['type']) {
      $stmt = $this->conn->prepare('update lot set description = null where user_id in (?)');
      $stmt->execute(array(implode(',', User::$inner_users)));
      $stmt->closeCursor();

      $stmt = $this->conn->prepare('select id from lot where user_id in (?)');
      $stmt->execute(array(implode(',', User::$inner_users)));
    }
    elseif ('all' == $arguments['type']) {
      $stmt = $this->conn->prepare('select id from lot');
      $stmt->execute(array());
    }
    else {
      $stmt = $this->conn->prepare('select id from lot where type = ?');
      $stmt->execute(array($arguments['type']));
    }

    $method = $client->getMethodName('synonymize_description');
    $count = 0;
    while ($id = $stmt->fetch(Doctrine::FETCH_COLUMN)) {
      $this->logSection('lot', $id);
      $client->addTask($method, $id);
      if (0 == ++$count % 50) {
        $client->runTasks();
      }
    }
    $client->runTasks();
  }

  public function logTask(GearmanTask $task)
  {
    if (!strpos($task->data(), ':')) {
      return false;
    }
    list($id, $text) = explode(':', $task->data(), 2);

    $this->logSection('description', $id);
    $this->log($text);
  }
}
