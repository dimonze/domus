<?php

class updateSlugForPostsTask extends sfBaseTask
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
    $this->name             = 'updateSlugForPosts';
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
    
    $types = Post::$types;
    // http://dev.garin.su/issues/15240 list only
    unset( 
      $types['events'],
      $types['qa'],
      $types['questionnaire']
    );
    
    $limit = intval($options['packsize']);
    $query = $conn->createQuery()
            ->from('Post p')
            ->whereIn('p.post_type', array_keys($types))
            ->andWhere('LENGTH(TRIM(p.slug)) = 0')
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
      $posts = $cq->execute();

      foreach ($posts as $post) {
        try{
          $post->setSlug('');
          $post->save();
        }catch(Exception $e){
          $errors[] = $post->getId();
          $this->logSection('post', "{$post->getId()} updating error");
        }
      }

      $updated += count($posts) == $limit ? $limit : count($posts);
      $this->logSection('general', "$updated/$count posts were processed");
      $posts->free();
      $cq->free();
    }
    
    $this->logSection('general', count($errors)." errors occurred." . ( count($errors) ? ' Error ID\'s: '.implode(', ',$errors) : '' ));
    return $this->complete($start);
  }
  
  protected function complete($start)
  {
    $this->logSection('general', "Execution time: " .(microtime(true)-$start) . ' second(s)');
    $this->logSection('general', "Peak memory usage: " . round(memory_get_peak_usage() / (1024 * 1024)) . 'MB');
  }
}
