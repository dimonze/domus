<?php

/**
 * Вынести города из районов
 *
 * @author kmad
 */
class moveCitiesTask extends sfBaseTask {

  public function configure() {

    $this->namespace        = 'domus';
    $this->name             = 'moveCities';
    $this->briefDescription = 'Move cities out of regions';
  }

  public function execute($arguments = array(), $options = array()) {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);

    $conn = Doctrine_Manager::connection();
    $q = $conn->prepare("UPDATE regionnode SET parent=NULL WHERE socr IN('г','пгт')");
    $q->execute();
    $this->log('Affected rows: ' . $q->rowCount());
  }

}
?>
