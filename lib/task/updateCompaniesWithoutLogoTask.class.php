<?php

class updateCompaniesWithoutLogoTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),      
    ));

    $this->namespace        = 'domus';
    $this->name             = 'updateCompaniesWithoutLogo';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [updateCompaniesWithoutLogo|INFO] task does things.
Call it with:

  [php symfony updateCompaniesWithoutLogo|INFO]
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
    $stmt = $conn->prepare('SELECT id FROM user WHERE type = ? AND (deleted = 0 OR deleted IS NULL)');
    $stmt->execute(array('company'));
    $i = 0;
    while ($id = $stmt->fetchColumn()) {
      $this->logSection('user', 'User #' . $id);
      $user = Doctrine::getTable('User')->find($id);
      if ($user){
        if (!$user->photo){
          $settings = array();
          foreach ($user->Settings as $setting){
            if ($setting->name == 'show_rating'){
              $setting->value = 0;
            }
            $settings[] = $setting->name;
          }
          if (!in_array('show_rating', $settings)){
            $user->setSettingsValue('show_rating', null);
          }
          $user->save();
        }
        $user->free();
        $this->logSection('user', 'User #' . $id . ' update Settings COMPLETE');
      }
    }
    $end_mic = microtime(true);
    $time_work = $end_mic - $start_mic;
    echo "Time Work: " . $time_work . " sec" . PHP_EOL;
  }
}
