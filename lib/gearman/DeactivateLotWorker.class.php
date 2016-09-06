<?php
class DeactivateLotWorker extends sfGearmanWorker
{
  public
    $name = 'deactivate-lot',
    $methods = array(
      'deactivate_lot'
    );

  public function doDeactivateLot (GearmanJob $job)
  {
    $this->startJob();

    if ($lot = Doctrine::getTable('Lot')->find($job->workload())){
      if ($lot->User->getSettingsValue('expire_notify')) {
        $response = serialize(array(
          $lot->user_id, $lot->type, $lot->id, $lot->address_full,
          $lot->User->email, $lot->User->name)
        );
      }
      else {
        $response = null;
      }

      $lot->deactivate()->save();
      $lot->free(true);

      return $this->completeJob($job, $response);
    }
    else {
      return $this->completeJob($job, 'Lot deleted or is not valid.');
    }
  }
}
?>