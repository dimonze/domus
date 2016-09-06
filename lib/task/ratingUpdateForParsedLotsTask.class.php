<?php
/**
 * Updates currency
 *
 * @package    domus
 * @subpackage task
 */
class ratingUpdateForParsedLotsTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('type', null, sfCommandOption::PARAMETER_OPTIONAL, 'Lot type (string value)', null),
    ));
    
    $this->namespace = 'rating';
    $this->name = 'ratingUpdateForParsedLots';
    $this->briefDescription = 'Updates rating for parsed lots';
    $this->detailedDescription = '';
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    ini_set('memory_limit', '2048M');
    $start = microtime(true);
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();

    $client = sfGearmanProxy::getClient();
    $client->setCompleteCallback(array($this, 'getLotStatus'));
    
    $lot_type = $options['type'];
    if(!array_key_exists($lot_type, Lot::$types)) $lot_type = null;
    
    $lots = $conn->prepare('
      SELECT l.id FROM lot l
      LEFT JOIN user u ON u.id = l.user_id
      WHERE l.status = ?
        AND (l.deleted_at = ? OR l.deleted_at IS NULL)
        AND (u.deleted_at = ? OR u.deleted_at IS NULL)
        AND (u.inactive = ? OR u.inactive IS NULL)
        AND (u.type = ? OR l.parsed_at IS NOT NULL)' . ( empty($lot_type) ? '' : " AND l.type = '$lot_type'" ));
    
    $lots->execute(array('active', 0, 0, 0, 'source'));
    $total = $lots->rowCount();
    $this->logSection('Total', $total);
    
    for ($i = 1; $id = $lots->fetchColumn(); $i++) {
      $client->addTask(
        $client->getMethodName('rerate_lot'),
        serialize(array('i' => $i, 'lot_id' => $id, 'total' => $total))
      );
      if ($i % 5000 == 0) {
        $this->logSection('gearman', 'Rerate lots part');
        $client->runTasks();
      }
    }
    $this->logSection('gearman', 'Rerate end lots part');
    $client->runTasks();
    $this->logSection('rerate', 'DONE        100%');
  }

  public function getLotStatus($task)
  {
    if (null != $task->data()) {
      $response = unserialize($task->data());
      $this->logSection('rerate', $response['text'], null, 'INFO');
    }
  }
}
