<?php

class updateSlugForLotsTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      // add your own options here
    ));

    $this->namespace        = 'domus';
    $this->name             = 'updateSlugForLots';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [domus:updateSlugForLots|INFO] task does things.
Call it with:

  [php symfony domus:updateSlugForLots|INFO]
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
      ->andWhere('status != ?')
      ->andWhere('status != ?')
      ->andWhere('status != ?')
      ->andWhere('region_id = ?')
      ->andWhere('deleted_at IS NULL')
      ->andWhere('slug is NULL')
      ->execute(array('inactive', 'restricted', 'moderate', 77), Doctrine::HYDRATE_SINGLE_SCALAR);
    $total = count($ids);
    $this->logSection('Total', $total);
    $i = 0;

    foreach ($ids as $id) {
      $lot = Doctrine::getTable('Lot')->find($id);

      $slug = array();
      foreach(explode(',', $lot->address1) as $part) {
        $part = Regionnode::unformatName($part);
        $slug[] = $part[0];
      }
      $slug = sprintf('%s %s %d', implode(' ', $slug), $lot->address2, $lot->id);
      $slug = Toolkit::slugify($slug);
      $lot->slug = $slug;
      $lot->save();

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
