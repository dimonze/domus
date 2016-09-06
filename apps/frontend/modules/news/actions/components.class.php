<?php

/**
 * news components.
 *
 * @package    domus
 * @subpackage news
 * @author     Garin Studio
 * @version    SVN: $Id: components.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class newsComponents extends sfComponents
{
  public function executeMarketgid()
  {
    $module = $this->getContext()->getModuleName();
    $this->show = false;

    if (in_array($module, array('news', 'posts', 'page', 'blogs', 'comments', 'expert_article', 'author_article'))) {
      $this->show = true;
    }
  }
  
  public function executeList()
  {
    $this->primary_news = Doctrine::getTable('Post')->createQuery('p')
      ->leftJoin('p.PostRegion r')
      ->separateThemes()
      ->andWhere('p.post_type = ?', 'news')
      ->andWhere('p.is_primary = ?', 1)
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
      ->andWhere('p.status = ?', 'publish')
      ->andWhere('r.region_id = ?', sfContext::getInstance()->getUser()->current_region->id)
      ->orderBy('p.created_at desc')
      ->fetchOne();

    $news = Doctrine::getTable('Post')->createQuery('p')
      ->select('p.title, p.lid')
      ->leftJoin('p.PostRegion r')
      ->separateThemes()
      ->andWhere('p.post_type = ?', 'news')
      ->andWhere('p.on_main = ?', 1)      
      ->andWhere('p.status = ?', 'publish')
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
      ->andWhere('r.region_id = ?', sfContext::getInstance()->getUser()->current_region->id)
      ->limit(sfConfig::get('sf_news_on_main', 2))
      ->orderBy('p.created_at desc');
    if (null != $this->primary_news) {
      $news->andWhere('p.id != ?', $this->primary_news->id);
    }
    $this->news = $news->execute();
  }

  public function executeThemeslist()
  {
    if (!isset($this->news_section, $this->theme_id, $this->theme)){
      return false;
    }
    
    $query = Doctrine::getTable('Post')->createQuery('p')
      ->select('p.title, p.lid')
      ->leftJoin('p.PostRegion r')
      ->leftJoin('p.PostTheme t')
      //->andWhere(PostQuery::getWhereExpression('t.theme_id'))
      ->andWhere('p.post_type = ?', 'news');
    if (isset($this->news_id)){
      $query->andWhere('p.id <> ?', $this->news_id);
    }
    $query->andWhere('p.status = ?', 'publish')
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
      ->andWhere('p.section = ?', News::$sections[$this->news_section])
      ->andWhere('t.theme_id = ?', $this->theme_id)
      ->andWhere('r.region_id = ?', sfContext::getInstance()->getUser()->current_region->id)
      ->limit(sfConfig::get('sf_news_themes_block', 5))
      ->orderBy('p.created_at desc');    
    $this->news = $query->execute();
  }

  public function executeSectionlist ()
  {
    if (!isset($this->news_section, $this->url)){
      return false;
    }
    
    $this->news = Doctrine::getTable('Post')->createQuery('p')
      ->select('p.title, p.lid')
      ->leftJoin('p.PostRegion r')
      ->separateThemes()
      ->andWhere('p.post_type = ?', 'news')      
      ->andWhere('p.status = ?', 'publish')
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
      ->andWhere('p.section = ?', $this->news_section)
      ->andWhere('r.region_id = ?', sfContext::getInstance()->getUser()->current_region->id)
      ->limit(sfConfig::get('sf_news_section_block', 3))
      ->orderBy('p.created_at desc')
      ->execute();    
  }

  public function executeListforposts ()
  {
    $this->news = Doctrine::getTable('Post')->createQuery('p')
      ->select('p.title, p.lid')
      ->leftJoin('p.PostRegion r')
      ->separateThemes()
      ->andWhere('p.post_type = ?', 'news')      
      ->andWhere('p.status = ?', 'publish')
      ->andWhere('p.is_primary = ?', 0)
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
      ->andWhere('r.region_id = ?', sfContext::getInstance()->getUser()->current_region->id)
      ->limit(sfConfig::get('sf_news_on_main', 3))
      ->orderBy('p.created_at desc')
      ->execute();


    $this->primary_news = Doctrine::getTable('Post')->createQuery('p')
      ->andWhere('p.post_type = ?', 'news')
      ->separateThemes()
      ->andWhere('p.is_primary = ?', 1)
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
      ->andWhere('p.status = ?', 'publish')
      ->orderBy('p.created_at desc')
      ->fetchOne();
  }

  public function executePortallatest ()
  {    
    $query = Doctrine::getTable('Post')->createQuery('p')
      ->select('p.title, p.lid')
      ->leftJoin('p.PostRegion r')
      ->separateThemes()
      ->andWhere('p.post_type = ?', 'news')
      ->andWhere('p.section = ?', 'Новости портала')      
      ->andWhere('p.status = ?', 'publish')
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'));
    if (isset($this->news_id)){
      $query->andWhere('p.id <> ?', $this->news_id);
    }
    $query->andWhere('r.region_id = ?', sfContext::getInstance()->getUser()->current_region->id)
      ->limit(sfConfig::get('sf_news_portal_latest_on_main', 10))
      ->orderBy('p.created_at desc');
      $this->news = $query->execute();
  }

  public function executePortalothernews ()
  {
    $query = Doctrine::getTable('Post')->createQuery('p')
      ->select('p.title, p.lid')
      ->leftJoin('p.PostRegion r')
      ->separateThemes()
      ->andWhere('p.post_type = ?', 'news')
      ->andWhere('p.section = ?', 'Новости портала')      
      ->andWhere('p.status = ?', 'publish')
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'));
    if (isset($this->news_id)){
      $query->andWhere('p.id <> ?', $this->news_id);
    }
    $query->andWhere('r.region_id = ?', sfContext::getInstance()->getUser()->current_region->id)
      ->orderBy('p.created_at desc');

    $this->pager = new sfDoctrinePager('Post', 10);
    $this->pager->setQuery($query);
    $this->pager->setPage(isset($this->page) ? $this->page : 1);
    $this->pager->init();
  }
}