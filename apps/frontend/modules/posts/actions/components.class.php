<?php

/**
 * posts components.
 *
 * @package    domus
 * @subpackage posts
 * @author     Garin Studio
 * @version    SVN: $Id: components.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class postsComponents extends sfComponents
{
  public function executeList()
  {
    if (!isset($this->post_type)) {
      return false;
    }
    $this->posts = Doctrine::getTable('Post')->createQuery('p')
      ->select('p.title, p.lid, p.title_photo')
      ->leftJoin('p.PostRegion r')
      ->separateThemes()
      ->andWhere('p.post_type = ?', $this->post_type)
      ->andWhere('p.on_main = ?', 1)      
      ->andWhere('p.status = ?', 'publish')
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
      ->andWhere('r.region_id = ?', sfContext::getInstance()->getUser()->current_region->id)
      ->limit(sfConfig::get('sf_posts_on_main', 3))
      ->orderBy('p.created_at desc')
      ->execute();

    if (sfConfig::get('is_new_building')) {
      $this->type_name = Post::$seo_types_nb[$this->post_type];
    }else {
      $this->type_name = Post::$seo_types[$this->post_type];
    }
  }

  public function executeThemes()
  {
    if (!isset($this->post_type, $this->theme_id, $this->theme)) {
      return false;
    }
    $this->type_name = Post::$types[$this->post_type];
    $query = Doctrine::getTable('Post')->createQuery('p')
      ->select('p.title, p.lid')
      ->leftJoin('p.PostRegion r')
      ->leftJoin('p.PostTheme t')
      ->andWhere(PostQuery::getWhereExpression('t.theme_id'))
      ->andWhere('p.post_type = ?', $this->post_type)      
      ->andWhere('p.status = ?', 'publish')
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
      ->andWhere('t.theme_id = ?', $this->theme_id);
    if (isset($this->post_id)){
      $query->andWhere('p.id <> ?', $this->post_id);
    }
      $query->andWhere('r.region_id = ?', sfContext::getInstance()->getUser()->current_region->id)
      ->limit(sfConfig::get('sf_posts_themes_block', 5))
      ->orderBy('p.created_at desc');
      $this->posts = $query->execute();
  }

  public function executeEventsonhome ()
  {
    $this->posts = Doctrine::getTable('Post')->createQuery('p')
      ->select('p.title, p.lid, p.title_photo')
      ->leftJoin('p.PostRegion r')
      ->separateThemes()
      ->andWhere('p.post_type = ?', 'events')
      ->andWhere('p.on_main = ?', 1)
      ->andWhere('p.status = ?', 'publish')
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
      ->andWhere('r.region_id = ?', sfContext::getInstance()->getUser()->current_region->id)
      ->limit(sfConfig::get('sf_posts_on_main', 3))
      ->orderBy('p.created_at desc')
      ->execute();
    $this->post_type = 'events';
    $this->type_name = Post::$types['events'];
  }

  public function executePostsonhome()
  {
    if (!isset($this->post_type)) {
      return false;
    }
    $this->posts = Doctrine::getTable('Post')->createQuery('p')
      ->select('p.title, p.lid, p.title_photo')
      ->leftJoin('p.PostRegion r')
      ->separateThemes()
      ->andWhere('p.post_type = ?', $this->post_type)
      ->andWhere('p.on_main = ?', 1)
      ->andWhere('p.status = ?', 'publish')
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
      ->andWhere('r.region_id = ?', sfContext::getInstance()->getUser()->current_region->id)
      ->limit(sfConfig::get('sf_posts_on_main', 3))
      ->orderBy('p.created_at desc')
      ->execute();

    if (sfConfig::get('is_new_building')) {
      $this->type_name = Post::$seo_types_nb[$this->post_type];
    }else {
      $this->type_name = Post::$seo_types[$this->post_type];
    }
  }
}