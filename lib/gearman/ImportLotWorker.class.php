<?php
class ImportLotWorker extends sfGearmanWorker
{
  public
    $name = 'import-lot',
    $methods = array('import_lot');

  protected function configure()
  {
    ini_set('memory_limit', '1024M');
    ini_set('max_execution_time', 0);
  }

  public function doImportLot(GearmanJob $job) {
    $this->startJob();
    $data = unserialize($job->workload());

    ImportXML::mesto($data);

    $this->completeJob();
  }
}
