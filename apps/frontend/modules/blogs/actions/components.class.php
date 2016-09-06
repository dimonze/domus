<?php

/**
 * author_article components.
 *
 * @package    domus
 * @subpackage author_article
 * @author     Garin Studio
 * @version    SVN: $Id: components.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class BlogsComponents extends sfComponents
{
  public function executeList()
  {    
    $this->blogs = Doctrine::getTable('Blog')->getActiveBlogs();
  }

  public function executeAuthors()
  {
    $this->authors = Doctrine::getTable('PostAuthor')->createQuery('a')
      ->select('a.id, a.name')
      ->leftJoin('a.Post p with p.status = ? and p.post_type = ?', array('publish', 'author_article'))
      ->andWhere('a.author_type = ?', 'author')
      ->orderBy('a.name asc')
      ->execute();
  }

  public function executeThemes()
  {
    if (!isset($this->theme_id, $this->theme)) {
      return false;
    }
    $this->articles = Doctrine::getTable('Post')->createQuery('p')
      ->select('p.title, p.lid')      
      ->leftJoin('p.PostTheme t')
      ->leftJoin('p.PostRegion r')
      ->andWhere('p.post_type = ?', 'author_article')
      ->andWhere('p.on_main = ?', 1)
      ->andWhere('p.status = ?', 'publish')
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
      ->andWhere('t.theme_id = ?', $this->theme_id)
      ->andWhere('r.region_id = ?', sfContext::getInstance()->getUser()->current_region->id)
      ->limit(sfConfig::get('sf_author_article_themes_block', 5))
      ->orderBy('p.created_at desc')
      ->execute();
  }

  public function executeOtherposts()
  {
    if (!isset($this->author_id, $this->article_id)) {
      return false;
    }

    $this->articles = Doctrine::getTable('Post')->createQuery('p')
      ->select('p.title, p.lid, p.created_at, a.photo, a.name')
      ->leftJoin('p.PostAuthor a')
      ->leftJoin('p.PostRegion r')
      ->andWhere('p.author_id = ?', $this->author_id)
      ->andWhere('p.post_type = ?', 'author_article')
      ->andWhere('p.id <> ?', $this->article_id)
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
      ->andWhere('r.region_id = ?', sfContext::getInstance()->getUser()->current_region->id)
      ->orderBy('p.created_at desc')
      ->limit(3)
      ->execute();
  }

  public function executePostList() {
    $this->posts = Doctrine::getTable('BlogPost')->createQuery('p')->
    select('*')->
    addSelect('(SELECT count(*) FROM BlogPostComment pc WHERE pc.post_id = p.id) as comments_count')->
    orderBy('comments_count DESC')->
    limit(3)->
    execute();
  }
}