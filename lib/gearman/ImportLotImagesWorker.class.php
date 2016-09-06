<?php
class ImportLotImagesWorker extends sfGearmanWorker
{
  public
    $name = 'import-lot-images',
    $methods = array('import_lot_images');

  protected function configure()
  {
    ini_set('memory_limit', '512M');
    ini_set('max_execution_time', 0);
  }

  public function doImportLotImages(GearmanJob $job) {
    $this->startJob();
    $data = unserialize($job->workload());

    $start = microtime(true);
    $lot = Doctrine::getTable('Lot')->find($data['lot_id']);
    if ($lot) {
      ImportFile::loadImages($lot, $data['image_urls']);
    }

    $end = microtime(true);
    $time = $end - $start;

    echo 'Images saved for Lot in: ' . $time . ' sec.' . PHP_EOL;

    $this->completeJob();
  }
}
