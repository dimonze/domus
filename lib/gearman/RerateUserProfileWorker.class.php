<?php
class RerateUserProfileWorker extends sfGearmanWorker
{
  public
    $name = 'rerate-user-profile',
    $methods = array(
      'rerate_user_profile'
    );

  public function doRerateUserProfile (GearmanJob $job)
  {
    $this->startJob();

    $user_id = unserialize($job->workload());
    if (!($user = Doctrine::getTable('User')->find($user_id))) {
      return $this->completeJob($job, serialize(
          array('user_id' => $user_id, 'rating' => 'NO USER')
        )
      );
    }

    if (in_array($user->type, User::$rated_user_types)) {
      $lots_rates = 0;
      $previous_rating = 0;
      $lots = Doctrine::getTable('Lot')->createQueryActive()
        ->select('rating, status')
        ->andWhere('user_id = ?', $user->id)
        ->andWhere('deleted_at = ? or deleted_at IS NULL', 0)
        ->execute();
      if ($lots){
        foreach ($lots as $lot){
          $lots_rates += Rating::getLotRate($lot);
          $lot->free();
        }
      }

      $previous_rating = $user->rating;
      $new_rate = Rating::calculate($user) + $lots_rates + sfConfig::get('app_user_promotion') * $user->Info->promotion;
      if(!$new_rate || $new_rate < 0) $new_rate = 0;
      $user->rating = $new_rate;

      if ($user->type == 'employee' && $user->rating != $previous_rating) {
        $user->Employer->rating += $user->rating - $previous_rating;
      }

      try {
        $user->save();
        return $this->completeJob($job, serialize(
            array('user_id' => $user->id, 'rating' => $user->rating)
          )
        );
      }

      catch (Exception $e) {
        return $this->completeJob($job, serialize(
            array('user_id' => $user->id, 'rating' => $new_rate . " Can't save")
          )
        );
      }
    }
    else {
      return $this->completeJob($job, serialize(
          array('user_id' => $user->id, 'rating' => 'NO Rated user type')
        )
      );
    }
  }
}
