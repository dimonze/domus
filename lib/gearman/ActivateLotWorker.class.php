<?php
class ActivateLotWorker extends sfGearmanWorker
{
  public
    $name = 'activate-lot',
    $methods = array(
      'activate_lot'
    );

  public function doActivateLot (GearmanJob $job)
  {
    $this->startJob();

    $workload = unserialize($job->workload());
    if ($lot = Doctrine::getTable('Lot')->find($workload['id'])) {
      $lot->active_till = date(
        'Y-m-d H:i:s',
        strtotime(' +' . (90 + $workload['active_till_day']) . ' days')
      );
      $lot->updated_at = date('Y-m-d H:i:s');
      $lot->status = 'active';
      $lot->save();

      $response = serialize(array(
        'id'          =>  $lot->id,
        'status'      =>  $lot->status,
        'active_till' =>  $lot->active_till
      ));

      $lot->free(true);


      return $this->completeJob($job, $response);
    }
    else {
      $response = serialize(array(
        'id'          =>  $workload['id'],
        'status'      =>  null,
        'active_till' =>  null
      ));
      return $this->completeJob($job, $response);
    }
  }
}
?>