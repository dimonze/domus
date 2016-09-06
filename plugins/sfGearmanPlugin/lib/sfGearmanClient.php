<?php

class sfGearmanClient extends GearmanClient
{
  protected static $_instance;
  protected $_project_name;

  public function __construct($servers = null)
  {
    parent::__construct();

    $this->_project_name = basename(sfConfig::get('sf_root_dir'));
    $this->initializeGearman($servers);
  }

  protected function initializeGearman($servers = null)
  {
    $this->addServers($servers ?: sfConfig::get('app_gearman_servers', '127.0.0.1:4730'));
  }

  public function getMethodName($name)
  {
    return sprintf('%s__%s', $this->_project_name, $name);
  }

  public function queue($method, $workload = null)
  {
    if ($workload && !is_scalar($workload)) {
      $workload = serialize($workload);
    }

    $this->doBackground($this->getMethodName($method), $workload);
  }
}