<?php

class updateNewbuildingSlug14543Task extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine')
    ));

    $this->namespace        = 'domus';
    $this->name             = 'updateNewbuildingSlug14543';
    $this->briefDescription = 'Update slug for Newbildings by #14543 rules. Applies only with #14543';
    $this->detailedDescription = <<<EOF
The [domus:updateNewbuildingSlug14543|INFO] task does things.
Call it with:

  [php symfony domus:updateNewbuildingSlug14543|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();

    $start = array('time' => microtime(true), 'memory' => memory_get_usage());
    
    $ids = Doctrine::getTable('Lot')->createQuery()
            ->select('id')
            ->andWhere('type = ?')
            ->execute( array('new_building-sale'), Doctrine::HYDRATE_SINGLE_SCALAR );
    
    $total = count($ids);
    $this->logSection('Total', $total);
    $i = 0;
    
    foreach ($ids as $id) {
      $lot = Doctrine::getTable('Lot')->find($id);
      $lot->slug = '';
      if($lot) $lot->save();
      $this->logSection('Slug', "New slug for $id is {$lot->slug}");
      $lot->free(true);
      if(++$i % 10 == 0) {
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
