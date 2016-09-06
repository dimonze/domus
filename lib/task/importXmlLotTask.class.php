<?php

class importXmlLotTask extends sfBaseTask {

  protected function configure()
  {
    $this->namespace = 'domus';
    $this->name = 'importxmllot';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [importxmllot|INFO] task does things.
Call it with:
  [php symfony importxmllot|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    ini_set('max_execution_time', 0);
    ini_set('memory_limit', '512M');

    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();

    $stmt = $conn->prepare(
        'SELECT u.id, i.xml_url, i.xml_type
      FROM user u
      LEFT JOIN user_info i on u.id = i.user_id
      WHERE
        (u.deleted_at = 0 OR u.deleted_at IS NULL)
        AND (u.inactive = 0 OR u.inactive IS NULL)
        AND (i.xml_url IS NOT NULL)'
    );

    $stmt->execute(array());
    $i = 0;
    while ($user = $stmt->fetchObject()) {
      $this->logSection('user', 'User #' . $user->id);
      sfGearmanProxy::doBackground('import_lots_xml_parse',
        array(
          'user_id' => $user->id,
          'xml_url' => $user->xml_url,
          'xml_type' => $user->xml_type,
        )
      );
    }
  }

  protected function memstat($count)
  {
    $this->logSection('memory', 'current memory usage: ' . memory_get_usage() . ' on lot ' . $count);
  }

}