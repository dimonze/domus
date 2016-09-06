<?php
/**
 * Creates landing pages for nodes
 *
 * @package    domus
 * @subpackage task
 */
class createLandingPagesForNodesAsnObjectTypesTask extends sfBaseTask
{
  protected
    $file_src = null,
    $file_res = null,
    $config   = null,
    $_count   = 0,
    $_gearman_client = null,
    $start    = null;

  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('region',   sfCommandArgument::REQUIRED, 'Region(s) - int or many integers separated by commas e.g. 77,78'),
    ));

    ini_set('memory_limit', '4G');
    ini_set('max_execution_time', 0);
    $this->namespace = 'landing';
    $this->name = 'createNodesAndObjectTypes';
    $this->briefDescription = 'Creates landing pages for nodes';
    $this->detailedDescription = '';
    $this->start = time();
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->logSection('Start', $this->start);
    $region = explode(',', preg_replace('/[^,\d]/', '', $arguments['region']));
    gc_enable();
    $this->logSection('GC is', gc_enabled() ? 'enabled' : 'disabled');
    sleep(1);
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine::getConnectionByTableName('Region');

    $this->_gearman_client = sfGearmanProxy::getClient();
    $this->_gearman_client->setCompleteCallback(array($this, 'getJobCallback'));

    $regions = Doctrine::getTable('Region')->createQuery()
      ->andWhereIn('id', $region)
      ->execute();

    $this->logSection('>', 'Start process regions');
    foreach($regions as $region) {
      $this->processRegion($region);
      $this->processChildren($region);
    }
    $this->_gearman_client->runTasks();

    $this->logSection('Total', $this->_count);
  }

  private function processRegion($region)
  {
    $this->makePages($region->id, null);
  }

  private function processChildren($region)
  {
    $childrens = Doctrine::getTable('Regionnode')->createQuery()
      ->andWhere('region_id = ?', array($region->id))
      ->andWhere('list = ?', array(1))
      ->execute();
    foreach($childrens as $region_node) { 
      $this->makePages($region->id, $region_node->id);
    }
  }

  private function makePages($region_id, $region_node_id)
  {
    foreach (Lot::$types as $type => $type_id) {
      if ('new_building-sale' == $type) break;
      $object_types = MetaParse::getObjectTypes($type);
      foreach ($object_types as $object_type => $values) {
        $this->logSection('Landing', sprintf('RegionId: %d, RegionNodeId: %d, Type: %s, ObjectType: %s', $region_id, $region_node_id, $type, $object_type));
        if (null != $type && null != $object_type) {
          $this->_gearman_client->addTask($this->_gearman_client->getMethodName('create_landing_page'), serialize(array(
            'region_id'       => $region_id,
            'region_node_id'  => $region_node_id,
            'type'            => $type,
            'object_type'     => $object_type,
          )));
        }
      }

      if(0 === ++$this->_count % 1000) {
        gc_collect_cycles();
        $this->logSection('Memory', sprintf('%s MB', memory_get_usage()/1024/1024));
        $this->logSection('From start', sprintf('%s min', round((time() - $this->start) / 60, 2)));
        $this->_gearman_client->runTasks();
      }
    }
    $this->logSection($this->_count, implode(' - ', array($region_id, $region_node_id)));
  }
  
  public function getJobCallback($task)
  {
    if (null != $task->data()) {
      $this->logSection('result', $task->data());
    }
  }
}