<?php
class RerateLotWorker extends sfGearmanWorker
{
  public
    $name = 'rerate-lot',
    $methods = array(
      'rerate_lot'
    );

  public function doRerateLot (GearmanJob $job)
  {
    $info = unserialize($job->workload());

    $lot = Doctrine::getTable('Lot')->findOneById($info['lot_id']);
    if ($lot) {
      $lot->rating = 0;
      $lot->save();

      $response = array(
        'text' =>
          'Lot #' . $info['lot_id']
          . '  | saved ' . $info['i'] . ' of ' . $info['total']
          . '  |  ' . ceil($info['i']/$info['total']*100) . '%'
          . '  |  ETA: '
          . date(
            'i:s',
            (ceil((microtime(true) - $start) / ceil($info['i']/$info['total']*100) * 100))
            - ceil(microtime(true) - $start)
          )
      );
    }
    else {
      $response = array('text' => 'NO LOT FOUND');
    }
    return $this->completeJob($job, serialize($response));
  }
}
