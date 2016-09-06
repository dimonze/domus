<?php

class ImportLotsFromSourcesTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      // add your own options here
    ));

    $this->namespace        = 'import';
    $this->name             = 'ImportLotsFromSources';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [ImportLotsFromSources|INFO] task does things.
Call it with:

  [php symfony ImportLotsFromSources|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    ini_set('memory_limit', '1024M');
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();

    $client = sfGearmanProxy::getClient();
    $client->setCompleteCallback(array($this, 'getImportResult'));

    $links = $conn->prepare(
      'SELECT ul.url, ul.type, ul.user_id, ul.file_type, u.type as user_type
       FROM user_sources_link ul
       LEFT JOIN user u ON u.id = ul.user_id
       WHERE u.type = ?'
    );
    $links->execute(array('company'));
    $count = 0;
    while ($link = $links->fetch()) {
      $this->logSection('file', 'Type: ' . $link['user_type']. ' ' . $link['url'] .' ' . $link['type'] . ' User: ' . $link['user_id']);
      if ($link['file_type'] == ImportFile::FILE_TYPE_XML) {
        $this->logSection('import', 'Import XML file: ' . $link['url']);

      }
      else if ($link['file_type'] == ImportFile::FILE_TYPE_CSV) {
        $this->logSection('import', 'Import CSV file: ' . $link['url']);
        continue;
      }
      else if ($link['file_type'] == ImportFile::FILE_TYPE_XLS) {
        $this->logSection('import', 'Import XLS file: ' . $link['url']);
        $this->logSection('import', 'Import XLS file: ' . $link['url']);
        continue;
      }
      //create data for worker
      $worker_data = array(
        'path'      =>  $link['url'],
        'type'      =>  $link['file_type'],
        'format'    =>  $link['type'],
        'user_id'   =>  $link['user_id'],
        'file_name' =>  $link['url']
      );
      //add background task
      $client->addTask($client->getMethodName('import_file'), serialize($worker_data));
      $count++;

      if ($count % 100 == 0) {
        $client->runTasks();
      }
    }

    $client->runTasks();
  }

  public function getImportResult($task)
  {
    if (null != $task->data()) {
      $result = $task->data();
      $this->logSection('result', 'Import result: ' . ($result) ? 'SUCCESS' : 'FAIL');
    }
  }
}
