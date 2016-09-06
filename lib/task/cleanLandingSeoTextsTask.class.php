<?php

class cleanLandingSeoTextsTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('packsize', null, sfCommandOption::PARAMETER_OPTIONAL, 'Size of pack, wich will be fetched form DB for updating. Default 50', 50)
    ));

    $this->namespace        = 'domus';
    $this->name             = 'cleanLandingSeoTexts';
    $this->briefDescription = '';
    $this->detailedDescription = '';
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();
    $start = microtime(true);
    
    $limit = intval($options['packsize']);
    $query = $conn->createQuery()
            ->from('LandingPage lp')
            ->andWhere('LENGTH(TRIM(lp.seo_text)) > 0')
            ->limit($limit);
            
    $count = $query->count();
    if(!$count) {
      $this->logSection('general', "Nothing to process");
      return $this->complete($start);
    }
    
    $updated = 0;
    $errors = array();
    for($i=1;$i<=ceil($count/$limit);$i++){
      $cq = clone $query;
      $cq->offset( ($i-1) * $limit );
      $items = $cq->execute();
      
      foreach ($items as $item) {
        try{
          $html = $item->getSeoText();
          $html = $this->clean($html);
          $item->setSeoText($html);
          $item->save();
          $updated++;
        }catch(Exception $e){
          $errors[] = $item->getId();
          $this->logSection('landing page', "{$item->getId()} updating error");
        }
      }
      
      $this->logSection('general', "$updated/$count landing pages were processed");
      $items->free();
      $cq->free();
    }

    return $this->complete($start, $errors);
  }
  
  protected function complete($start, $errors = null)
  {
    if(!empty($errors))
      $this->logSection('general', count($errors)." errors occurred." . ( count($errors) ? ' Error ID\'s: '.implode(', ',$errors) : '' ));
    
    $this->logSection('general', "Execution time: " .(microtime(true)-$start) . ' second(s)');
    $this->logSection('general', "Peak memory usage: " . round(memory_get_peak_usage() / (1024 * 1024)) . 'MB');
  }
  
  protected function clean($html)
  {
    $html = preg_replace(array(
        '#<p>&nbsp;</p>[\r\n]*#m' //Empty paragraphs
    ), array(
        ''
    ), $html);
    
    return $html;
  }
}
