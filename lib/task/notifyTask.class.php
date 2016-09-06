<?php

class notifyTask extends sfBaseTask
{
  protected $routing, $sphinx_client;

  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('period', sfCommandArgument::REQUIRED),
    ));

    $this->addOptions(array(
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('app', 'frontend', sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
    ));

    $this->namespace        = 'domus';
    $this->name             = 'notify';
    $this->briefDescription = '';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    ini_set('memory_limit', '4096M');

    $this->createConfiguration($options['app'], 'dev');

    $databaseManager = new sfDatabaseManager($this->configuration);
    $this->connection = Doctrine_Manager::connection();

    $past = substr($arguments['period'], 0, strlen($arguments['period']) -2);
    $past = str_replace('dai', 'day', $past);
    $past = strtotime(sprintf('-1 %s', $past)) + 7 * 3600; # 7 hours cron shift
    
    $this->logSection('lot', 'send price notifications');

    // Lot
    $subscribers = $this->connection->prepare('SELECT email, pk FROM notification WHERE period = ? AND model = ? AND field = ? GROUP BY email');
    $subscribers->execute(array($arguments['period'], 'Lot', 'price'));

    if (count($subscribers)) {
      $this->fetchLot($past);
      while ($subscriber = $subscribers->fetch()) {
        //send task to GearmanProxy
        sfGearmanProxy::doBackground(
          'notify_price',
          serialize(
            array(
              'subscriber'  =>  $subscriber,
              'lots'        =>  $this->resultset,
              'period'      =>  $arguments['period']
        )));
      }
    }

    $this->logSection('search', 'send search notifications');

    // Search
    $subscribers = Doctrine::getTable('Notification')->createQuery()
      ->from('Notification n, Search s')
      ->select('n.*')
      ->andWhere('n.period = ?', $arguments['period'])
      ->andWhere('n.model = ?', 'Search')
      ->andWhere('s.id = n.pk')
      ->execute();

    if (count($subscribers)) {
      $messages = array();
      sfGearmanProxy::doBackground(
        'notify_search',
        serialize(
          array(
            'subscribers' =>  $subscribers,
            'period'      =>  $arguments['period']
      )));
    }
  }

  protected function fetchLot($from)
  {
    $query = Doctrine::getTable('Log')->createQuery()
      ->andWhere('model = ?', 'Lot')
      ->andWhere('field = ?', 'price')
      ->andWhere('created_at between ? and ?', array(
         date('Y-m-d H:i:s', $from),
         date('Y-m-d H:i:s')
        ))
      ->orderBy('created_at asc');

    $this->resultset = array();
    foreach ($query->fetchArray() as $row) {
      if (!isset($this->resultset[$row['pk']])) {
        $this->resultset[$row['pk']] = array('lot' => null, 'changes' => array());
      }
      $this->resultset[$row['pk']]['changes'][] = $row;
      
      $row = null;
    }
  }
}
