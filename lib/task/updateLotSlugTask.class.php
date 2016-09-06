<?php

class updateLotSlugTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('app', 'frontend', sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
    ));

    $this->namespace        = 'update';
    $this->name             = 'slug';
    $this->briefDescription = 'Fix incorrect slug for active lots';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();
    
    $start = microtime(true);    
    // $query = $conn->prepare("SELECT COUNT(*) 
    //   FROM `lot` 
    //   WHERE 
    //     `slug` LIKE ? 
    //     AND `status` = ? 
    //     AND (`deleted_at` = 0 OR `deleted_at` IS NULL)");
    // $query->execute(array('%-0', 'active'));
    // $found = $query->fetchColumn();
    // if(!empty($found)){
    //   $this->logSection('doctrine', "$found lots with incorrect slug found");
    //   $query = $conn->prepare("UPDATE `lot` 
    //     SET 
    //       `slug` = CONCAT(LEFT(`slug`,(LENGTH(`slug`)-1)),CAST(`id` AS CHAR)) 
    //     WHERE 
    //       `slug` LIKE ? 
    //       AND `status` = ? 
    //       AND (`deleted_at` = 0 OR `deleted_at` IS NULL)");
    //   if($query->execute(array('%-0', 'active'))) 
    //     $this->logSection('doctrine', "All lots were fixed successfully!");
    // } else
    //   $this->logSection('doctrine', "There is nothing to fix");

    $query = $conn->prepare("select id from lot where slug = ?");
    $query->execute(array('sluid'));

    while ($lot_id = $query->fetchColumn()) {
      $this->logSection('lot', 'Slug not correct in Lot #' . $lot_id);
      $lot = Doctrine::getTable('Lot')->findOneById($lot_id);
      if ($lot) {
        //Save Slug Only
        try {
          $lot->save();
          $lot->free();
        }
        catch(Exception $e) {
          $this->logSection('error', "Can't save Lot #$lot_id", 'ERROR');
        }

      }
    }
    $this->logSection('general', "Execution time: " .(microtime(true)-$start) . ' second(s)');
    $this->logSection('general', "Peak memory usage: " . round(memory_get_peak_usage() / (1024 * 1024)) . 'MB');
  }
}
