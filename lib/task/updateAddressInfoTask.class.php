<?php

class updateAddressInfoTask extends sfBaseTask
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
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      // add your own options here
    ));

    $this->namespace        = 'update';
    $this->name             = 'addressInfo';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [updateAddressInfo|INFO] task does things.
Call it with:

  [php symfony updateAddressInfo|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    ini_set('max_execution_time', 0);
    ini_set('memory_limit', '2048M');
    
    // initialize the database connection
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();
  }
}
