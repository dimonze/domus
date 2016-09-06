<?php

class ExportLotsToMitulaTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addArgument('host', sfCommandArgument::OPTIONAL, 'host name', 'mesto.ru');

    $this->namespace        = 'export';
    $this->name             = 'LotsToMitula';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [ExportLotsToMitula|INFO] task does things.
Call it with:

  [php symfony ExportLotsToMitula|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    ini_set('max_execution_time', 0);
    ini_set('memory_limit', '4096M');

    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();

    $client = sfGearmanProxy::getClient();

    $context->getConfiguration()->loadHelpers('Url');

    $files_50_77 = array(
       'mitula' =>  '8TqPnqgq9Aucm7ZRh7feCQFqH5wnofYZzzhYMhrlKvew9Um6u2',
       'spec'   =>  '8TqPnqgq9Aucm7ZRh7feCQFU45nFgp2ErkGKrn593Jfy54HbgP'
    );

    $files_all = array(
        'nd' => 'naydidom',
    );

    $files_list = array_merge($files_50_77, $files_all);

    $begin = '<?xml version="1.0" encoding="utf-8"?>' . "\n"
        . '<realty-feed xmlns="http://webmaster.yandex.ru/schemas/feed/realty/2010-06">' . "\n"
        . '<generation-date>' . date('c') . '</generation-date>' . "\n";

    foreach ($files_list as $tmp_file => $file_name) {
      $file = fopen(sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . $tmp_file . '.xml', 'w');
      $this->logSection('job', 'File ' . $tmp_file . ' opened');
      $this->logSection('job', 'Begin to write');

      fwrite($file, $begin);
      fclose($file);
    }

    $query = 'SELECT l.id, l.region_id FROM lot l
       WHERE l.status = ?
         AND (l.deleted_at = ? OR l.deleted_at IS NULL)
       ORDER BY l.region_id DESC, l.created_at DESC, l.active_till ASC';

    $lots = $conn->prepare($query);

    // select all good lots
    $lots->execute(array('active', 0));

    $this->logSection('lot', 'Available lots #' . $lots->rowCount());

    $i = 0;
    while ($lot = $lots->fetch(Doctrine::FETCH_ASSOC)) {
      $files = array();
      $files = array_merge($files, $files_all);

      if(in_array($lot['region_id'], array(50,77))){
        $files = array_merge($files, $files_50_77);
      }

      $client->addTask($client->getMethodName('export_lot_to_mitula'), serialize(array(
        'id' => $lot['id'],
        'host' => $arguments['host'],
        'files' => $files,
      )));

      if (++$i % 5000 == 0) {
        $this->logSection('job', 'Run gearman tasks');
        $client->runTasks();
      }
      $lot = null;
    }
    $this->logSection('job', 'Run last gearman tasks');
    $client->runTasks();


    foreach ($files_list as $tmp_file => $file_name) {
      $file = fopen(sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . $tmp_file . '.xml', 'a');
      fwrite($file, PHP_EOL. '</realty-feed>');
      fclose($file);

      unlink(sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . $file_name . '.xml');
      rename(
        sfConfig::get('sf_data_dir')    . DIRECTORY_SEPARATOR . $tmp_file   . '.xml',
        sfConfig::get('sf_upload_dir')  . DIRECTORY_SEPARATOR . $file_name  . '.xml'
      );
    }

    $this->logSection('job', 'File closed. All is good');
  }

  protected function memstat($count) {
    $this->logSection('memory', 'Current memory usage: ' . memory_get_usage() . ' on lot ' . $count);
  }
}
