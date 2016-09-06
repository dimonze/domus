<?php

class CollectRegionNodeStatisticTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
    ));

    $this->namespace        = 'collect';
    $this->name             = 'RegionNodeStatistic';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [ExportRegionNodeStatistic|INFO] task does things.
Call it with:

  [php symfony ExportRegionNodeStatistic|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    ini_set('max_execution_time', 0);
    ini_set('memory_limit', '2048M');
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    sfContext::createInstance($configuration);
    new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();

    $file_name = 'collect-geo-statistic';

    $client = sfGearmanProxy::getClient();
    $client->setCompleteCallback(array($this, 'collectingResult'));

    $regions = $conn->prepare('select id from region');
    $regions->execute();

    $this->logSection('1.', 'Getting Regions...');

    while($region = $regions->fetchColumn()) {
      $this->logSection('2.', 'Region #' . $region . ': collecting statistics...');
      $client->addTask(
        $client->getMethodName('collect_region_statistic'),
        serialize(array('region_id' => $region, 'file_name' => $file_name))
      );

      $nodes = $conn->prepare('select id from regionnode where region_id = ? and parent IS NULL');
      $nodes->execute(array($region));

      if ($nodes->columnCount()) {
        while($node = $nodes->fetchColumn()) {
          $this->logSection('3.', 'RegionNode #' . $node . ': collectiong statistics... ');
          $client->addTask(
            $client->getMethodName('collect_regionnode_statistic'),
            serialize(array('region_id' => $region ,'node_id' => $node, 'file_name' => $file_name))
          );
        }

        $client->runTasks();
      }
    }

    $client->runTasks();
  }

  public function collectingResult($task)
  {
    $this->log($task->data());
  }
}
