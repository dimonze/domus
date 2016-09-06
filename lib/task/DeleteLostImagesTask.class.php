<?php
/* Magic file */

class DeleteLostImagesTask extends sfBaseTask {
  public function configure() {

    $this->namespace        = 'domus';
    $this->name             = 'deletelostimages';
  }
  
  public function execute($arguments = array(), $options = array()){
    $this->logSection('Start execute','');
    
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);

    $conn = Doctrine::getConnectionByTableName('Lot');
    $res = $conn
    ->execute('SELECT id, images FROM lot WHERE images IS NOT NULL AND images != "" AND status = ? ',array('active'))
    ->fetchAll(Doctrine::FETCH_ASSOC);
    
    chdir(__DIR__.'/../../web/');
    $this->logSection('Cwd', getcwd());
    $counters = array('total_rows' => 0, 'fixed_rows' => 0, 'lost_imgs' => 0);
    foreach($res as $lot){
      $path = Toolkit::buildStoragePath('lot', $lot['id'],true).'/source/';
      $images = explode(',', trim($lot['images'], ','));
      $exist = array();
      foreach($images as $img){
        if(is_file($path.$img)){
          $exist[] = $img;
        }
      }
      $diff = count($images) - count($exist);
      
      if($diff > 0){
        $counters['fixed_rows']++;
        $counters['lost_imgs']+=$diff;
        $this->logSection('id='.$lot['id'], 'diff='.$diff);
        $exist = is_array($exist) && count($exist) ? implode(',',$exist) : null;
        $conn->execute('UPDATE lot SET images = ? WHERE id = ?',array($exist, $lot['id']));
      }
      $counters['total_rows']++;
    }
    foreach($counters as $k=>$v){
      $this->logSection($k,$v);
    }
  }
}