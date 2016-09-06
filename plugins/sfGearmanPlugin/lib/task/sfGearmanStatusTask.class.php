<?php

class sfGearmanStatusTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('all', null, sfCommandOption::PARAMETER_NONE, 'Do not filter methods'),
    ));

    $this->namespace        = 'gearman';
    $this->name             = 'status';
    $this->briefDescription = null;
    $this->detailedDescription = null;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $servers = explode(',', sfConfig::get('app_gearman_servers', '127.0.0.1:4730'));
    $project_name = basename(sfConfig::get('sf_root_dir'));

    foreach ($servers as $server) {
      $server = explode(':', $server);
      if (empty($server[1])) {
        $server[1] = 4730;
      }

      $this->logSection('server', sprintf('%s:%d', $server[0], $server[1]));
      $this->log(sprintf('%-40s %-10s %-10s %-10s', 'method', 'total', 'running', 'available'));

      $socket = fsockopen($server[0], $server[1]);

      if (!is_resource($socket)) {
        throw new Exception(sprintf('Failed connect to server %s:%d', $server[0], $server[1]));
      }

      fwrite($socket, "status\r\n");
      $response = '';
      while (!strpos($response, "\n.")) {
        $response .= fread($socket, 2048);
      }
      fclose($socket);

      foreach (explode("\n", $response) as $line) {
        if ($options['all'] || 0 === strpos($line, $project_name . '__')) {
          $line = explode("\t", $line);
          if (4 == count($line)) {
            $this->log(sprintf('%-40s %-10d %-10d %-10d', $line[0], $line[1], $line[2], $line[3]));
          }
        }
      }
    }
  }
}
