<?php

class updateUserLotsCounterTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
    ));

    $this->namespace        = 'domus';
    $this->name             = 'updateUserLotsCounter';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [updateUserLotsCounter|INFO] task does things.
Call it with:

  [php symfony updateUserLotsCounter|INFO]
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
    $stmt = $conn->prepare('SELECT id FROM user');
    $stmt->execute(array());
    $i = 0;
    while ($id = $stmt->fetchColumn()) {
      $this->logSection('user', 'User #' . $id);
      $active = $conn->prepare(
        'SELECT COUNT(id)
         FROM lot
         WHERE (deleted = 0 OR deleted IS NULL)
         AND user_id = ? AND status = ?');
      $active->execute(array($id, 'active'));
      if ($active) {
        $active_lots = $active->fetchColumn();
      }
      else {
        $active_lots = 0;
      }
      $this->logSection('lots-count', 'Lots=' . $active_lots);
      $user = $conn->prepare(
        'UPDATE user
         SET active_count = ?
         WHERE id = ?');
      $user->execute(array($active_lots, $id));

      $this->logSection('user-lots-count', sprintf(
        'User #%d, active Lots: %d',
        $id, $active_lots
      ));
      unset($user, $id);
    }
    $this->logSection('user-lots-count', 'COMPLETE');
    $end_mic = microtime(true);
    $time_work = $end_mic - $start_mic;
    echo "Time Work: " . $time_work . " sec" . PHP_EOL;
  }
}