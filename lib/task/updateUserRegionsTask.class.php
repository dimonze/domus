<?php

class updateUserRegionsTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),      
    ));

    $this->namespace        = 'domus';
    $this->name             = 'updateUserRegions';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [updateUserRegions|INFO] task does things.
Call it with:

  [php symfony updateUserRegions|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();

    ini_set('memory_limit', '1024M');
    ini_set('max_execution_time', 0);
    $start_mic = microtime(true);
    $stmt = $conn->prepare(
      'SELECT id
      FROM user
      WHERE
        type IN (?, ?, ?)
        AND (deleted = 0 OR deleted IS NULL)
        AND (inactive = 0 OR inactive IS NULL)'
    );
    $stmt->execute(array('company', 'realtor', 'employee'));
    while($id = $stmt->fetchColumn()) {
      $this->logSection('user', 'User #' . $id);
      $user = Doctrine::getTable('User')->find($id);
      if ($user){
        $this->logSection('user', 'User #' . $id);
        $regions = $conn->prepare(
          'SELECT region_id
          FROM lot
          WHERE
            status = ?
            AND user_id = ?
            AND (deleted = ? OR deleted IS NULL)');
        $regions->execute(array('active', $id, 0));        
        $user_regions = $regions->fetchAll(Doctrine::FETCH_COLUMN);        
        if (count($user_regions) > 0) {
          foreach ($user_regions as $region){
            $this->logSection('update-region', 'Set Region #' . $region . ' to User #' . $user->id);
            $user->setRegion($region);            
          }
        }
        $this->logSection('user', 'User #' . $id . ' SUCCESS');
      }
    }

    $end_mic = microtime(true);
    $time_work = $end_mic - $start_mic;
    echo "Time Work: " . $time_work . " sec" . PHP_EOL;
  }
}
