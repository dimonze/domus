<?php

/**
 * expert_article actions.
 *
 * @package    domus
 * @subpackage expert_article
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class expert_articleActions extends sfActions
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
    $user = $this->getUser();
    if ($request->hasParameter('sort_order')){
      $order = $request->getParameter('sort_order');
    }else {
      $order = 'created_at-desc';
    }
    switch ($order){
      case 'created_at-desc':
        $sort_order = 'p.created_at desc';
        $this->sort_order_date = 'asc';
        break;
      case 'created_at':
        $sort_order = 'p.created_at asc';
        $this->sort_order_date = 'desc';
        break;
      case 'author-desc':
        $sort_order = 'a.name desc';
        $this->sort_order_author = 'asc';
          break;
      case 'author':
        $sort_order = 'a.name asc';
        $this->sort_order_author = 'desc';
          break;
      default:
        $sort_order = 'a.name asc';
    }

    $authors = Doctrine::getTable('PostAuthor')->createQuery('a')
      ->select('a.*')
      ->leftJoin('a.Post p')
      ->leftJoin('p.PostRegion r')
      ->leftJoin('p.PostTheme t')
      ->where('a.author_type = ?', 'expert') 
      ->andWhere(PostQuery::getWhereExpression('t.theme_id'))
      ->andWhere('p.post_type = ?', 'expert_article')
      ->andWhere('r.region_id = ?', $user->current_region->id)
      ->andWhere('p.created_at = (SELECT pp.created_at FROM post pp WHERE pp.author_id = a.id AND pp.post_type = ? AND (pp.deleted_at = 0 OR pp.deleted_at IS NULL) ORDER BY pp.created_at DESC LIMIT 1)','expert_article')
      ->groupBy('a.id')
      ->orderBy($sort_order);
    $this->pager = new sfDoctrinePager('PostAuthor', sfConfig::get('app_authors_my_max_per_page', 3));
    $this->pager->setQuery($authors);
    $this->pager->setPage($request->getParameter('page', 1));
    $this->pager->init();

    $this->cache_prefix = sprintf('%d_%d_%d_',
      $request->getCookie('js_on'),
      $this->getUser()->current_region->id,
      sfConfig::get('is_new_building')
    );
    $this->setLayout('homepage');
  }

  public function executeShow (sfWebRequest $request)
  {
    $this->forward404Unless(($request->hasParameter('id') || $request->hasParameter('slug')) && $request->hasParameter('author_id'));
    
    $author_id = $request->getParameter('author_id');
    $cid = $request->getParameter('id', null);
    $slug = $request->getParameter('slug', null);
    $page = $request->getParameter('page', 1);
    
    if(!empty($slug)){
      preg_match('#^(\w+[\w\-\.]*)\-(\d+)(\-part(\d+))*$#', $slug, $matches);
      $slug = $matches[1];
      $cid = $matches[2];
      if(isset($matches[4])) $page = intval($matches[4]);
    }

    $this->article = Doctrine::getTable('Post')->find($cid);
    $this->forward404Unless($this->article);
    $this->forward404If($this->article->status == 'not_publish');
    $this->forward404If($this->article->created_at > date('Y-m-d H:i:s'));
    
    if(empty($slug) && !empty($this->article->slug)){
      $this->redirect($this->generateUrl('expert_article_show_slug', array(
          'author_id' => $author_id,
          'slug' => "{$this->article->slug}-$cid"
          . (!empty($page) && $page > 1 ? '-part'.$page : '' )
      )), 301);
    }
    
    if (!$this->article->title_h1) {
      sfConfig::set('no_metas', true);
    }
    $user = $this->getUser();
    
    sfContext::getInstance()->getConfiguration()->loadHelpers('Domus');
    
    $this->paginate = paginate_by_hr($this->article->post_text, $page);
    $this->forward404Unless(($page > 0 && $page <= $this->paginate['total']));

    $this->post_themes = $this->article->getThemesArray();
    $this->author_id = $request->getParameter('author_id');
    $this->author = Doctrine::getTable('PostAuthor')->find($this->author_id);
    $this->cache_prefix = sprintf('%s_%d_author_%s_%d',
      'expert_article',
      $request->getCookie('js_on'),
      $this->author->id,
      sfConfig::get('is_new_building')
    );

    $this->setLayout('homepage');
    $this->getResponse()->addMeta('title', $this->article->title_h1);
    $this->getResponse()->addMeta('description', $this->article->description);
    $this->getResponse()->addMeta('keywords', $this->article->keywords);
    if($canonical = $this->article->canonical_url) {
      $this->getResponse()->addMeta('canonical', $canonical);
    }
  }

  public function executeShowauthor(sfWebRequest $request)
  {
    $this->forward404Unless($request->hasParameter('author_id'));
    $user = $this->getUser();

    $this->author = Doctrine::getTable('PostAuthor')->find($request->getParameter('author_id'));
    $this->forward404Unless($this->author);

    $query = Doctrine::getTable('Post')->createQuery('p')
      ->select('p.created_at, p.lid, p.title')
      ->leftJoin('p.PostRegion r')
      ->where('p.post_type = ?', 'expert_article')
      ->separateThemes()
      ->andWhere('p.author_id = ?', $this->author->id)
      ->andWhere('r.region_id = ?', $user->current_region->id)
      ->orderBy('p.created_at desc');
    $this->pager = new sfDoctrinePager('Post', 6);
    $this->pager->setQuery($query);
    $this->pager->setPage($request->getParameter('page', 1));
    $this->pager->init();
    $this->cache_prefix = sprintf('%s_%d_author_%s_',
                                  'expert_article',
                                  $request->getCookie('js_on'),
                                  $this->author->id);
    $this->setLayout('homepage');
  }

  public function executeTheme(sfWebRequest $request)
  {
    $this->forward404Unless($request->hasParameter('theme'));
    $user = $this->getUser();

    $trans_tbl = DomusSearchRoute::$translit_table;
    $this->theme = $request->getParameter('theme');
    $theme = str_replace(array_values($trans_tbl), array_keys($trans_tbl), $this->theme);
    $this->post_theme = Doctrine::getTable('Theme')->createQuery()
      ->select('title, id')
      ->andWhere('title = ?', $theme)
      ->fetchOne();

    $this->current_theme = $this->post_theme->id;
    $this->forward404Unless($this->post_theme);

    $this->cache_prefix = sprintf('expert_article_%d_%d_',
                                  $request->getCookie('js_on'),
                                  $this->post_theme->id
                                  );
    $query = Doctrine::getTable('Post')->createQuery('p')
      ->separateThemes()
      ->leftJoin('p.PostRegion r')
      ->leftJoin('p.PostTheme tt')
      ->leftJoin('p.PostAuthor a')
      ->andWhere('p.post_type = ?', 'expert_article')
      ->andWhere('p.status = ?', 'publish')
      ->andWhere('tt.theme_id = ?', $this->post_theme->id)
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
      ->andWhere('r.region_id = ?', $user->current_region->id)
      ->orderBy('p.created_at desc');

    $this->pager = new sfDoctrinePager('Post', sfConfig::get('app_posts_my_max_per_page', 10));
    $this->pager->setQuery($query);
    $this->pager->setPage($request->getParameter('page', 1));
    $this->pager->init();

    $this->setLayout('homepage');
  }
}
