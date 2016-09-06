<?php

/**
 * qa components.
 *
 * @package    domus
 * @subpackage news
 * @author     Garin Studio
 * @version    SVN: $Id: components.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class qaComponents extends sfComponents
{
  public function executeList()
  {
    $this->qa = Doctrine::getTable('Post')->createQueryActive('p')
      ->select('p.title, p.lid')
      ->andWhere('p.post_type = ?', 'qa')
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
      ->limit(sfConfig::get('app_qa_max_q_on_sidebar', 3))
      ->orderBy('p.created_at desc')
      ->execute();
  }

  public function executeThemeslist()
  {
    if (!isset($this->theme_id, $this->theme)){
      return false;
    }

    $query = Doctrine::getTable('Post')->createQueryActive('p')
      ->select('p.title, p.lid')
      ->leftJoin('p.PostRegion r')
      ->leftJoin('p.PostTheme t')
      ->andWhere('p.post_type = ?', 'qa');
    if (isset($this->qa_id)){
      $query->andWhere('p.id <> ?', $this->qa_id);
    }
    $query->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))      
      ->andWhere('t.theme_id = ?', $this->theme_id)
      ->andWhere('r.region_id = ?', sfContext::getInstance()->getUser()->current_region->id)
      ->limit(sfConfig::get('sf_qa_themes_block', 5))
      ->orderBy('p.created_at desc');    
    $this->qa = $query->execute();
  }

  public function executeListforposts ()
  {
    $this->news = Doctrine::getTable('Post')->createQueryActive('p')
      ->select('p.title, p.lid')
      ->leftJoin('p.PostRegion r')
      ->andWhere('p.post_type = ?', 'qa')
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
      ->andWhere('r.region_id = ?', sfContext::getInstance()->getUser()->current_region->id)
      ->limit(sfConfig::get('sf_qa_on_main', 3))
      ->orderBy('p.created_at desc')
      ->execute();
  }
}