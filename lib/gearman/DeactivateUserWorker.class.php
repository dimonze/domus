<?php
class DeactivateUserWorker extends sfGearmanWorker
{
  public
    $name = 'decativate-user',
    $methods = array(
      'deactivate_user'
    );

  public function doDeactivateUser (GearmanJob $job)
  {
    $this->startJob();

    if (!($user = Doctrine::getTable('User')->find(unserialize($job->workload())))) {
      return $this->completeJob($job, 'NO USER');
    }

    $lots = Doctrine::getTable('Lot')->createQuery()
      ->where('user_id = ?', $user->id)
      ->execute();

    foreach ($lots as $lot){
      $lot->deactivate()->save();
      $lot->free(true);
    }

    Doctrine::getTable('Favourite')->createQuery()
      ->delete()
      ->where('user_id = ?', $user->id)
      ->execute();

    $user->inactive = true;
    $user->save();
    $user->free(true);

    return $this->completeJob($job, 'SUCCESS');
  }
}