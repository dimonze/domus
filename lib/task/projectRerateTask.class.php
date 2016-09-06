<?php

/**
 * @package    domus
 * @subpackage task
 */
class ProjectRerateTask extends sfBaseTask
{
  protected
    $_current_stat = 0,
    $_current_stat_time = null,
    $_stat = array(),
    $_data = array();

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->namespace = 'domus';
    $this->name = 'rerate';
    $this->briefDescription = '';
    $this->detailedDescription = '';

    $this->addArgument('mode', sfCommandArgument::REQUIRED, 'full|update');
    $this->addOption('force');
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();

    ini_set('memory_limit', '1024M');
    $start_mic = microtime(true);
    if ($arguments['mode'] == 'full') {
      $job = Job::get('rating');
      if (!$job->canStart() && empty($options['force'])) {
        return null;
      }
      $job->start();
    }

    try {
      $client = sfGearmanProxy::getClient();
      $client->setCompleteCallback(array($this, 'getLotRating'));

      if ($arguments['mode'] == 'full') {
        $stmt = $conn->prepare('select id from lot where status = ? and (deleted_at = ? or deleted_at IS NULL)');
        $stmt->execute(array('active', 0));

        $i = 0;
        while ($id = $stmt->fetchColumn()) {
          $client->addTask($client->getMethodName('rerate_lot'), $id);
          if (++$i == 5000) {
            $i = 0;
            $this->log('Running tasks');
            $client->runTasks();
          }
        }
      }
      elseif ($arguments['mode'] == 'update') {
        $stmt = $conn->prepare(
          'SELECT lot.id
          FROM lot
          INNER JOIN user ON lot.user_id = user.id
          WHERE lot.status = ? AND (lot.deleted_at = ? OR lot.deleted_at IS NULL)
            AND (user.type <> ? AND user.type <> ?)
            AND (user.deleted_at = ? OR user.deleted_at IS NULL)
            AND (user.inactive = ? OR user.inactive IS NULL)'
        );
        $stmt->execute(array('active', 0, 'owner', 'source', 0, 0));
        $i = 0;
        while ($id = $stmt->fetchColumn()) {
          $client->addTask($client->getMethodName('rerate_lot'), $id);
          if (++$i == 5000) {
            $i = 0;
            $this->log('Running tasks');
            $client->runTasks();
          }
        }
      }
      else {
        throw new Exception(sprintf('Unknown mode "%s"', $arguments['mode']));
      }
      //Run all not running tasks
      $this->log('Running all not running tasks');
      $client->runTasks();

      $this->logSection('users', PHP_EOL . 'Select Users');

      $client->setCompleteCallback(array($this, 'getUserRating'));
      $users = $conn->prepare(
        'SELECT id
        FROM user
        WHERE (inactive = ? OR inactive IS NULL)
          AND (deleted_at = ? OR deleted_at IS NULL)
          AND (type <> ? AND type <> ?)
        ORDER BY type = ?'
      );
      $users->execute(array('0', '0', 'owner', 'source', 'company'));
      $i = 0;
      while ($id = $users->fetchColumn()) {
        $this->logSection('users', 'User #' . $id);
        $lots_rate = isset($this->_data[$id]) ? $this->_data[$id] : 0;
        $client->addTask($client->getMethodName('rerate_user'), serialize(array($id, $lots_rate)));
      }
      $this->log('Running users tasks');
      $client->runTasks();
    }
    catch (Exception $e) {
      if (isset($job)) {
        $job->fail();
      }
      throw $e;
    }

    if (isset($job)) {
      $job->finish();
    }
    $end_mic = microtime(true);
    $time_work = $end_mic - $start_mic;
    echo "Time Work: " . $time_work . " sec" . PHP_EOL;
  }

  public function getLotRating($task)
  {
    $time = time();
    if ($this->_current_stat_time != $time) {
      if ($this->_current_stat_time) {
        $this->_stat[] = $this->_current_stat;
      }
      $this->_current_stat_time = $time;
      $this->_current_stat = 0;
    }
    $this->_current_stat++;

    if (count($data = explode(':', $task->data())) != 4){
      $this->logSection('error', sprintf('lot: %s', $task->data()), null, 'ERROR');
    }
    else {
      list($lot_id, $lot_rating, $user_id, $lot_rate) = explode(':', $task->data());
      if (isset($this->_data[$user_id])){
        $this->_data[$user_id] += (int) $lot_rate;
      }
      else {
        $this->_data[$user_id] = (int) $lot_rate;
      }

      $avg = !empty($this->_stat) ? array_sum($this->_stat) / count($this->_stat) : 0;
      $this->logSection('rating', sprintf('lot: %d rating: %d / avg: %.2f lots/sec', $lot_id, $lot_rating, $avg));
    }
  }

  public function getUserRating($task)
  {
    $data = explode(':', $task->data());
    if (count($data) == 3){
      $this->logSection('rating', sprintf('user: %d rating: %d (%d)',
      $data[1], $data[2], ($data[0] != '') ? $data[0] : 0));
      unset($this->_data[$data[1]]);
      $this->logSection('lots-array', 'Lots rates: ' . count($this->_data));
    }
    else {
      $this->logSection('users', sprintf('user: %s', $task->data()), null, 'ERROR');
    }
  }
}
