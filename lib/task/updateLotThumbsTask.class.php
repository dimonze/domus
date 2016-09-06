<?php

class updateLotThumbsTask extends sfBaseTask
{
  protected function configure()
  {

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),      
    ));

    $this->namespace        = 'domus';
    $this->name             = 'updateLotThumbs';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [updateLotThumbs|INFO] task does things.
Call it with:

  [php symfony updateLotThumbs|INFO]
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
      FROM lot
      WHERE images LIKE ? AND thumb IS NULL');
    $stmt->execute(array('1%'));
    $i = 0;
    while ($id = $stmt->fetchColumn()) {
      $this->logSection('lot', 'Lot #' . $id . ' update.');
      $update = $conn->prepare('UPDATE lot SET thumb = ? WHERE id = ?');
      $update->execute(array(1, $id));
      $this->logSection('lot', 'Lot #' . $id . ' UPDATE SUCCESS.');
    }
  }
}
