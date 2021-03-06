<?php

/**
 * expert_article components.
 *
 * @package    domus
 * @subpackage expert_article
 * @author     Garin Studio
 * @version    SVN: $Id: components.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class expert_articleComponents extends sfComponents
{
  public function executeList()
  {    
    $this->articles = Doctrine::getTable('Post')->createQuery('p')
      ->select('p.title, p.lid, a.name, a.company')
      ->separateThemes()
      ->leftJoin('p.PostAuthor a')
      ->leftJoin('p.PostRegion r')
      ->andWhere('a.author_type = ?', 'expert')
      ->andWhere('p.post_type = ?', 'expert_article')
      ->andWhere('p.on_main = ?', 1)
      ->andWhere('p.status = ?', 'publish')
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
      ->andWhere('r.region_id = ?', sfContext::getInstance()->getUser()->current_region->id)
      ->limit(sfConfig::get('sf_expert_article_on_main', 3))
      ->orderBy('p.created_at desc')
      ->execute();
  }

  public function executeAuthors()
  {
    $this->authors = Doctrine::getTable('PostAuthor')->createQuery('a')
      ->select('a.id, a.name')
      ->leftJoin('a.Post p with p.status = ? and p.post_type = ?', array('publish', 'expert_article'))
      ->andWhere('a.author_type = ?', 'expert')
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
      ->leftJoin('p.PostRegion r')
      ->leftJoin('p.PostTheme t')
      ->andWhere(PostQuery::getWhereExpression('t.theme_id'))
      ->andWhere('p.post_type = ?', 'expert_article')      
      ->andWhere('p.status = ?', 'publish')
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
      ->andWhere('t.theme_id = ?', $this->theme_id)
      ->andWhere('r.region_id = ?', sfContext::getInstance()->getUser()->current_region->id)
      ->limit(sfConfig::get('sf_expert_article_themes_block', 5))
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
      ->separateThemes()
      ->andWhere('p.author_id = ?', $this->author_id)
      ->andWhere('p.post_type = ?', 'expert_article')
      ->andWhere('p.id <> ?', $this->article_id)
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
      ->andWhere('r.region_id = ?', sfContext::getInstance()->getUser()->current_region->id)
      ->orderBy('p.created_at desc')
      ->limit(3)
      ->execute();
  }
}