<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class PostAuthor extends BasePostAuthor
{
  public function getAuthorsTableProxy()
  {
    return Doctrine::getTable('PostAuthor')->createQuery()
      ->andWhere('author_type = ?', 'author')
      ->execute();
  }

  public function getExpertsTableProxy()
  {
    return Doctrine::getTable('PostAuthor')->createQuery()
      ->andWhere('author_type = ?', 'expert')
      ->execute();
  }

  public function getPhotoPath($create = true)
  {
    return Toolkit::buildStoragePath('author', $this->id, true, $create);
  }

  public function getFullPhotoPath($create = true, $source = true)
  {
    return Toolkit::buildStoragePath('author', $this->id, false, $create, true);
  }

  public function getLatestPosts()
  {
    $user = sfContext::getInstance()->getUser();
    $cache = new DomusCache();

    $key = sprintf ('%s_%s_%s_latest_posts', $this->author_type, $this->id, $user->current_region->id, sfConfig::get('is_new_building'));
    if ($cache->has($key)){
      return unserialize($cache->get($key));
    }
    else {
      $posts = Doctrine::getTable('Post')->createQuery('p')
        ->select('p.lid, p.title, p.title_photo')
        ->leftJoin('p.PostRegion r')
        ->separateThemes()
        ->andWhere('p.author_id = ?', $this->id)
        ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
        ->andWhere('r.region_id = ?', $user->current_region->id)
        ->orderBy('p.created_at desc')
        ->limit(4)
        ->execute();
      $cache->set($key, serialize($posts), 20 * 60);
      return $posts;
    }
  }
}