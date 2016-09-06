<?php
if (empty($argv[1]) || empty($argv[2]) || !file_exists($argv[2])) {
  die('Usage: php sfGearmanWorkerStarter class file [application] [env] [doctrine_connection_name]');
}

require dirname(__FILE__) . '/../../config/ProjectConfiguration.class.php';
$project_configuration = new ProjectConfiguration();

require $argv[2];

$worker = new $argv[1](
    $project_configuration,
    empty($argv[3]) ? null : $argv[3],
    empty($argv[4]) ? null : $argv[4],
    empty($argv[5]) ? null : $argv[5]
  );
$worker->run();