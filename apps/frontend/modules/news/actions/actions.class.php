<?php

/**
 * news actions.
 *
 * @package    domus
 * @subpackage news
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class newsActions extends sfActions
{
  public function postExecute()
  {
    MetaParse::setMetas($this);
  }
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->forward404Unless($request->hasParameter('news_section'));
    $user = $this->getUser();
    $this->news_section = $request->getParameter('news_section');
    $news_section = News::$sections[$this->news_section];

    if ($this->news_section == 'news-market'){
      $this->primary_news = Doctrine::getTable('Post')->createQuery('p')
        ->separateThemes()
        ->leftJoin('p.PostRegion r')
        ->andWhere('p.post_type = ?', 'news')
        ->andWhere('p.is_primary = ?', 1)
        ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
        ->andWhere('p.status = ?', 'publish')
        ->andWhere('r.region_id = ?', sfContext::getInstance()->getUser()->current_region->id)
        ->orderBy('p.created_at desc')
        ->fetchOne();
    }

    $query = Doctrine::getTable('Post')->createQuery('p')
      ->select('p.title, p.lid')
      ->separateThemes()
      ->leftJoin('p.PostRegion r')
      ->andWhere('p.post_type = ?', 'news')
      ->andWhere('p.on_main = ?', 1)
      ->andWhere('p.status = ?', 'publish')
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
      ->andWhere('r.region_id = ?', sfContext::getInstance()->getUser()->current_region->id)
      ->andWhere('p.section = ?', $news_section);

    if (null != $this->primary_news) {
      $query->andWhere('p.id != ?', $this->primary_news->id);
    }
    if ($this->news_section == 'news-market'
        || $this->news_section == 'news-portal'){
      $query->limit(sfConfig::get('sf_news_on_main', 3));
    }
    else {
      $query->limit(sfConfig::get('sf_news_on_main', 4));
    }
    $query->orderBy('p.created_at desc');
    $this->news = $query->execute();
    $this->themes = Theme::getThemesArray();
    $this->current_section = $this->news_section;
    $this->sections = array_diff(Post::$sections, array($this->news_section => $news_section));
    $this->cache_prefix = sprintf('%d_%d_%d_',
      $request->getCookie('js_on'),
      $this->getUser()->current_region->id,
      sfConfig::get('is_new_building')
    );
    $this->page = $request->getParameter('page');
    $this->setLayout('homepage');
  }


  public function executeShow(sfWebRequest $request)
  {
    $user = $this->getUser();
    $cid = $request->getParameter('id',null);
    $slug = $request->getParameter('slug',null);
    $this->forward404Unless( (!empty($cid) || !empty($slug)) );
    if( $slug ){
      preg_match('#^(\w+[\w\.-]*)-(\d+)$#', $slug, $matches);
      $slug = $matches[1];
      $cid = $matches[2];
    }
    $query = Doctrine::getTable('Post')->createQuery()
      ->andWhere('id = ?', $cid)
      ->andWhere('post_type = ?', 'news');
    if(!empty($slug)) $query->andWhere('slug = ?', $slug);
    $this->news = $query->fetchOne();
    $this->forward404Unless($this->news);
    $this->forward404If($this->news->status != 'publish');
    
    if(empty($slug) && !empty($this->news->slug)){
      $this->redirect($this->generateUrl('news_show_slug', array(
          'slug' => "{$this->news->slug}-$cid"
      )), 301);
    }

    $this->cache_prefix = sprintf(
      'news_%d_%d_%d_',
      $request->getCookie('js_on'),
      $this->getUser()->current_region->id,
      sfConfig::get('is_new_building')
    );
    $this->themes = Theme::getThemesArray();
    $this->news_themes = $this->news->getThemesArray();
    $this->news_section = array_search($this->news->section, Post::$sections);
    $news_section[$this->news_section] = $this->news->section;
    $this->sections = array_diff(Post::$sections, $news_section);

    $this->setLayout('homepage');
    $this->getResponse()->addMeta('title', $this->news->title_seo ? $this->news->title_seo : $this->news->title_h1);
    $this->getResponse()->addMeta('description', $this->news->description);
    $this->getResponse()->addMeta('keywords', $this->news->keywords);
    if($canonical = $this->news->canonical_url) {
      $this->getResponse()->addMeta('canonical', $canonical);
    }
  }

  public function executeTheme (sfWebRequest $request)
  {
    $this->forward404Unless($request->hasParameter('news_section') && $request->hasParameter('theme'));
    $this->forward404Unless($request->getParameter('news_section') != 'news-portal');
    $user = $this->getUser();

    $trans_tbl = DomusSearchRoute::$translit_table;
    $this->theme = $request->getParameter('theme');
    $post_theme = str_replace(array_values($trans_tbl), array_keys($trans_tbl), $request->getParameter('theme'));
    $this->post_theme = Doctrine::getTable('Theme')->createQuery()
      ->select('title, id')
      ->andWhere('title = ?', $post_theme)
      ->fetchOne();
    $this->forward404Unless($this->post_theme);

    if ($request->hasParameter('created_at')){
      $created_at = $request->getParameter('created_at');
      $this->created_at = date('Y-m-d', strtotime($created_at));
    }
    else {
      $this->created_at = date('Y-m-d');
    }
    list($year, $month, $day) = explode('-', $this->created_at);
    $this->real_day = date('d');

    $this->news_section = $request->getParameter('news_section');
    $this->forward404Unless(array_key_exists($this->news_section, News::$sections));
    $news_section = News::$sections[$this->news_section];

    $this->themes = Theme::getThemesArray();
    $this->current_theme = $this->post_theme->id;
    $this->sections = array_diff(News::$sections, array($this->news_section, News::$sections[$this->news_section]));
    $this->cache_prefix = sprintf('news_%d_%d_%d_',
                                  $request->getCookie('js_on'),
                                  $this->getUser()->current_region->id,
                                  sfConfig::get('is_new_building'));
    $mm_years = Doctrine::getTable('Post')->getMaxMinYears(
      'news',
      $this->post_theme->id,
      $this->getUser()->current_region->id,
      $news_section
    );
    $this->years = range($mm_years['min_year'], $mm_years['max_year']);
    $this->months = Toolkit::$months;
    $this->current_year = $year ? $year : $mm_years['max_year'];
    $this->current_month = $month ? $month : 1;
    $this->current_day = $day ? $day : 1;

    $this->days = range(Toolkit::getMonthDays($this->current_year, $this->current_month), 1);
    $this->nb_items_per_day = Doctrine::getTable('Post')->getNbItemsPerDay(
      $this->current_year,
      $this->current_month,
      'news',
      $this->post_theme->id,
      $user->current_region->id,
      $news_section
    );
    $this->news = Doctrine::getTable('Post')->createQuery('p')
      ->select('p.title, p.lid, p.created_at')
      ->leftJoin('p.PostRegion r')
      ->leftJoin('p.PostTheme t')
      ->andWhere('p.post_type = ?', 'news')
      ->andWhere('p.status = ?', 'publish')
      ->andWhere('p.created_at like ?', $this->created_at . '%')
      ->andWhere('r.region_id = ?', $user->current_region->id)
      ->andWhere('t.theme_id = ?', $this->post_theme->id)
      ->andWhere('p.section = ?', $news_section)
      ->orderBy('p.created_at desc')
      ->execute();
    if (count($this->news) == 0){
      $this->news = Doctrine::getTable('Post')->getLatestNews(
        $news_section,
        $this->post_theme->id,
        $user->current_region->id
      );
    }

    $this->news_themes = array($this->post_theme->id => $this->post_theme->title);
    $this->setLayout('homepage');

  }
}
