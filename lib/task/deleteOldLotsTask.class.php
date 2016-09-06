<?php

class deleteOldLotsTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
    ));

    $this->namespace        = 'project';
    $this->name             = 'deleteOldLots';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [project:delete_old_lots|INFO] task does things.
Call it with:

  [php symfony project:delete_old_lots|INFO]
EOF;
    $this->conn             = null;
    $this->lot_info_conn    = null;
  }

  protected function execute($arguments = array(), $options = array())
  {
    ini_set('memory_limit', '4096M');
    
    $this->logSection('Start delete lots','');
    
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);

    $this->conn = Doctrine::getConnectionByTableName('Lot');
    $this->lot_info_conn = Doctrine::getConnectionByTableName('LotInfo');
    
    $period = date('Y-m-d H:i:s', strtotime(date('Y-m-d') . ' -3 months'));
    $this->logSection('Period', '> ' . $period);
    $res = $this->conn
      ->execute(
        'SELECT id FROM lot WHERE status = ? and updated_at <= ? and (deleted = ? OR deleted_at <= ?)',
        array('inactive', $period, 1, $period)
      )
      ->fetchAll(Doctrine::FETCH_ASSOC);
    
    chdir(__DIR__.'/../../web/');
    $this->logSection('Cwd', getcwd());
    
    $this->logSection('LOT TO DELETE', count($res));
    
    foreach($res as $lot){
      $this->removeLot($lot);
      $lot = null;
    }
    
    $this->logSection('DELETE PARTNERS LOTS', '');
    $period = date('Y-m-d H:i:s', strtotime(date('Y-m-d') . ' -1 months'));
    $lots = $this->conn
      ->execute(
        'SELECT l.id FROM lot l LEFT JOIN user u ON l.user_id = u.id 
        WHERE
          u.group_id = ? AND l.status = ? AND l.imported = ?
          AND updated_at <= ? and (deleted = ? OR deleted_at <= ?)
          LIMIT 100000', array(50, 'inactive', 1, $period, 1, $period))
      ->fetchAll(Doctrine::FETCH_ASSOC);
    
    foreach($lots as $lot) {
      $this->removeLot($lot);
      $lot = null;
    }
    
    $this->logSection('LOTS TO DELETE FROM PARTNERS', count($lots));
  }
  
  private function removeLot($lot)
  {
    $path = Toolkit::buildStoragePath('lot', $lot['id'],true);
    $this->logSection('Remove image dir', 'Unlink ' . $path);
    exec('rm -rf ' . $path);

    $this->logSection('DELETE LOT', '#' . $lot['id']);
    $this->conn->execute('DELETE FROM lot WHERE id = ?', array($lot['id']));

    $this->logSection('DELETE FROM LOT_INFO', 'LotId #' . $lot['id']);
    $this->lot_info_conn->execute('DELETE FROM lot_info WHERE lot_id = ?', array($lot['id']));

    $lot  = null;
    $path = null;
  }
}
