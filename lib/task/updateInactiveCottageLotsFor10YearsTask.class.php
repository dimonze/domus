<?php

class updateInactiveCottageLotsFor10YearsTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
    ));

    $this->namespace        = 'domus';
    $this->name             = 'updateInactiveCottageLotsFor10Years';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [domus:updateInactiveCottageLotsFor10Years|INFO] task does things.
Call it with:

  [php symfony domus:updateInactiveCottageLotsFor10Years|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();
    
    $start = array('time' => microtime(true), 'memory' => memory_get_usage());

    $ids = Doctrine::getTable('Lot')->createQuery('l')
      ->select('l.id')
      ->andWhere('l.type = ?', 'cottage-sale')
      ->andWhere('l.status = ?', 'inactive')
      ->andWhere('l.deleted_at IS NULL')
      ->execute(array(), Doctrine::HYDRATE_SINGLE_SCALAR);
    $total = count($ids);
    $this->logSection('Total', $total);
    $i = 0;

    foreach ($ids as $k => $id) {
      $lot = Doctrine::getTable('Lot')->find($id);
      $lot->status = 'active';
      $lot->active_till = date("Y-m-d H:m:s", strtotime("+10 years"));
      $lot->save();

      $lot->free(true);
      if((++$i % 10 == 0) || ($k == count($ids) - 1)) {
        $mem  = round((memory_get_usage() - $start['memory'])/1024/1024, 2);
        $time = round((microtime(true) - $start['time']), 2);
        $ips  = round($i/$time, 2);

        $eta  = round((($total - $i) / $ips) / 60, 2);

        $this->logSection('After', sprintf('%d entries spent %d Mb @ %s sec | ETA: %s min | Items per sec: %s',
          $i, $mem, $time, $eta, $ips
        ));
      }
    }
  }
}
