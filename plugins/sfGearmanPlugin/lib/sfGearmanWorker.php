<?php

abstract class sfGearmanWorker extends GearmanWorker
{
  public
    $name                    = null,
    $methods                 = array();

  protected
    $_project_name           = null,
    $_project_configuration  = null,
    $_configuration          = null,
    $_db_manager             = null,
    $_db_connection          = null,

    $_jobs_complete          = 0,
    $_gmm_client             = null;

  public function __construct(ProjectConfiguration $project_configuration, $application = null, $env = null, $db_connection_name = null)
  {
    parent::__construct();

    $this->_project_configuration = $project_configuration;
    $this->_project_name = basename(sfConfig::get('sf_root_dir'));

    $this->initialize($application, $env, $db_connection_name);
    $this->initializeGearman();

    $this->configure();
  }

  protected function initialize($application = null, $env = null, $db_connection_name = null)
  {
    if (null !== $application) {
      $this->_configuration = ProjectConfiguration::getApplicationConfiguration($application, $env, true);
    }

    if (null !== $env) {
      sfConfig::set('sf_environment', $env);
    }

    if ($db_connection_name) {
      $this->_db_manager = new sfDatabaseManager($this->_project_configuration);
      $this->_db_connection = $this->_db_manager->getDatabase($db_connection_name)->getConnection();
    }
  }

  protected function initializeGearman()
  {
    if (null === $this->name) {
      throw new Exception('Variable $name must be defined');
    }
    if (empty($this->methods)) {
      throw new Exception('$methods must have at least one element');
    }

    $servers = explode(',', sfConfig::get('app_gearman_servers', '127.0.0.1:4730'));
    shuffle($servers);
    $this->addServers(implode(',', $servers));

    foreach ($this->methods as $method) {
      $method_name = 'do' . sfInflector::camelize($method);
      $method = sprintf('%s__%s', $this->_project_name, $method);

      if (is_callable(array($this, $method_name))) {
        $this->addFunction($method, array($this, $method_name));
      }
      else {
        throw new Exception(sprintf('Method "%s" is not callble', $method_name));
      }
    }

    $this->_gmm_client = new sfGearmanClient('127.0.0.1');
  }

  protected function configure()
  { }

  public function run()
  {
    while ($this->work());
  }

  protected function startJob()
  {
    $this->_gmm_client->doHigh(
      'gmm_notify',
      sprintf('%d:%s:%d', getmypid(), 'processing', $this->_jobs_complete)
    );
  }

  protected function completeJob(GearmanJob $job = null, $complete = null)
  {
    ++$this->_jobs_complete;
    $result = $this->_gmm_client->do(
      'gmm_notify',
      sprintf('%d:%s:%d', getmypid(), 'complete', $this->_jobs_complete)
    );

    if ($job) {
      $job->sendComplete($complete);
    }

    if ($result == 'exit') {
      exit(0);
    }
  }

  /**
   * @return sfGearmanClient
   */
  protected function getClient()
  {
    return $this->_gmm_client;
  }
}
