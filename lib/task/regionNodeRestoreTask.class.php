<?php

/**
 * Restore region nodes
 * 
 * #5637
 *
 * @author kmad
 */
class regionNodeRestoreTask extends sfBaseTask {
  
  protected $backup_path;
  
  public function configure() {
    $this->addOptions(array(
      new sfCommandOption('csv_path', null, sfCommandOption::PARAMETER_REQUIRED, 'CSV path'),
    ));

    $this->namespace = 'domus';
    $this->name = 'regionnoderestore';
    $this->briefDescription = 'Restore region nodes';
  }

  public function execute($arguments = array(), $options = array()) {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    if (empty($options['csv_path'])) throw new Exception('Parameter "csv_path" is required');
    $this->csv_path = realpath($options['csv_path']);
    if (!is_file($this->csv_path)) throw new Exception('File does not exists');
    $f = fopen($this->csv_path, 'r');
    while ($a = fgetcsv($f)) {
      if ($a[3] == 'пгт') {
        $parent = 'NO PARENT';
        if (intval($a[4])) {
          $v = Doctrine_Manager::connection()->query('SELECT name FROM Regionnode WHERE id = ?', array($a[4]), Doctrine::HYDRATE_ARRAY);
          $parent = $v[0]['name'];
          if ($parent) {
            Doctrine_Manager::connection()->query('UPDATE Regionnode SET parent = ?  WHERE id = ?', array($a[4], $a[0]), Doctrine::HYDRATE_ARRAY);
          }
        }
        echo $a[3] . ' ' . $a[2] . ' parent: ' . $a[4] . '(' . $parent . ')' . PHP_EOL;
      }
    }
  }

}

?>
