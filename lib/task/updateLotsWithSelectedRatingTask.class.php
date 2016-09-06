<?php

class updateLotsWithSelectedRatingTask extends sfBaseTask
{
  protected $updated = 0;
  
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('rating', null, sfCommandOption::PARAMETER_OPTIONAL, 'Rating on which you want to update. Default 0', 0)
    ));

    $this->namespace        = 'domus';
    $this->name             = 'updateLotsWithSelectedRating';
    $this->briefDescription = '';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();
    $start_time = microtime(true);
    
    $client = sfGearmanProxy::getClient();
    $client->setCompleteCallback(array($this, 'getLotStatus'));
    
    $rating = intval($options['rating']);
    $query = $conn->createQuery()
            ->select('l.id')
            ->from('Lot l')
            ->where('l.rating = ?', $rating);

    $data = $query->fetchArray();
    $total = count($data);
    foreach ($data as $i => $cid) {
      $client->addTask(
        $client->getMethodName('rerate_lot'),
        serialize(array('i' => $i+1, 'lot_id' => $cid['id'], 'total' => $total))
      );

      if($i > 0 && $i % 500 == 0){
        $this->logSection('gearman', 'Rerate lots part');
        $client->runTasks();
      }
    }
    $this->logSection('gearman', 'Rerate end lots part');
    $client->runTasks();
    
    return $this->complete($total);
  }
  
  public function getLotStatus($task)
  {
    if (null != $task->data()) {
      $response = unserialize($task->data());
      $this->logSection('rerate', $response['text'], null, 'INFO');
    }
  }
  
  protected function complete($start_time)
  {
    $this->logSection('general', "Work time: ". round(microtime(true)-$start_time,2) .' second(s)');
    $this->logSection('general', "Peak memory usage: " . round(memory_get_peak_usage() / (1024 * 1024)) . 'MB');
  }
}
