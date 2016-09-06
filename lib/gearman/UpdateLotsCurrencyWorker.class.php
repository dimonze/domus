<?php
class UpdateLotsCurrencyWorker extends sfGearmanWorker
{
  public
    $name = 'update-currency',
    $methods = array(
      'update_lots_currency'
    );

  public function doUpdateLotsCurrency (GearmanJob $job)
  {
    $this->startJob();
    $config = unserialize($job->workload());
    $log = array();
    foreach ($config as $code => $rate)
    {
      $count = Doctrine_Query::create()->update('Lot l')->set('exchange', '?', $rate)
                  ->where('currency = ?', $code)->execute();
      $log[] = sprintf('Updated %d rows for %s', $count, $code);
    }
    return $this->completeJob($job, $log);
  }
}