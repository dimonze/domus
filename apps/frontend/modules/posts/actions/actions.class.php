<?php

/**
 * posts actions.
 *
 * @package    domus
 * @subpackage posts
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class postsActions extends sfActions
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
    $this->forward404Unless($request->hasParameter('post_type'));
    $post_type = $request->getParameter('post_type');
    $this->forward404Unless(array_key_exists($post_type, Post::$types));

    $user = $this->getUser();

    $this->posts = Doctrine::getTable('Post')->createQuery('p')
      ->select('p.title, p.lid, p.title_photo')
      ->leftJoin('p.PostRegion r')
      ->separateThemes()
      ->andWhere('p.post_type = ?', $post_type)
      ->andWhere('p.on_main = ?', 1)
      ->andWhere('p.status = ?', 'publish')
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
      ->andWhere('r.region_id = ?', sfContext::getInstance()->getUser()->current_region->id)
      ->limit(sfConfig::get('sf_posts_on_main', 3))
      ->orderBy('p.created_at desc')
      ->execute();

    $this->themes = Theme::getThemesArray();
    $this->other_posts = Post::getOtherPostTypes($post_type);
    $user->setAttribute('post_type', $post_type);
    $user->setAttribute('post_type_name', Post::$types[$post_type]);

    $this->cache_prefix = sprintf('%s_%d_%d_%d_',
      $post_type,
      $request->getCookie('js_on'),
      $this->getUser()->current_region->id,
      sfConfig::get('is_new_building')
    );
    $this->post_type = $post_type;
    $this->setLayout('homepage');
  }

  public function executeShow(sfWebRequest $request)
  {
    $this->forward404Unless($request->hasParameter('post_type') && ($request->hasParameter('id') || $request->hasParameter('slug')));
    
    $post_type = $request->getParameter('post_type');
    $cid = $request->getParameter('id', null);
    $slug = $request->getParameter('slug', null);
    $page = $request->getParameter('page', 1);
   
    if(!empty($slug)){
      preg_match('#^(\w+[\w\-\.]*)\-(\d+)(\-part(\d+))*$#', $slug, $matches);
      $slug = $matches[1];
      $cid = $matches[2];
      if(isset($matches[4])) $page = intval($matches[4]);
    }
    
    $user = $this->getUser();
    $query = Doctrine::getTable('Post')->createQuery('p')
      ->andWhere('p.id = ?', $cid)
      ->andWhere('p.post_type = ?', $post_type);
    if(!empty($slug)) $query->andWhere('p.slug = ?', $slug);
    
    $this->post = $query->fetchOne();
    $this->forward404Unless($this->post);
    
    if(empty($slug) && !empty($this->post->slug)){
      $routes = $this->getContext()->getRouting()->getRoutes();
      $route = array_key_exists($post_type.'_slug', $routes) ? $post_type.'_slug' : 'post_show_slug';
      $this->redirect($this->generateUrl($route, array(
          'post_type' => $post_type,
          'slug' => "{$this->post->slug}-$cid"
          . (!empty($page) && $page > 1 ? '-part'.$page : '' )
      )), 301);
    }
    
    sfContext::getInstance()->getConfiguration()->loadHelpers('Domus');
    
    $this->paginate = paginate_by_hr($this->post->post_text, $page);
    $this->forward404Unless(($page > 0 && $page <= $this->paginate['total']));

    $this->other_posts = Post::getOtherPostTypes($post_type);
    $this->post_themes = $this->post->getThemesArray();
    $this->cache_prefix = sprintf(
      '%s_%d_%d_%d_',
      $post_type,
      $request->getCookie('js_on'),
      $this->getUser()->current_region->id,
      sfConfig::get('is_new_building')
    );
    $this->post_type = $post_type;

    $this->setLayout('homepage');
    $this->getResponse()->addMeta('title', $this->post->title_h1);
    $this->getResponse()->addMeta('description', $this->post->description);
    $this->getResponse()->addMeta('keywords', $this->post->keywords);
    if($canonical = $this->post->canonical_url) {
      $this->getResponse()->addMeta('canonical', $canonical);
    }
  }

  public function executeTheme (sfWebRequest $request)
  {
    $this->forward404Unless($request->hasParameter('post_type') && $request->hasParameter('theme'));
    $post_type = $request->getParameter('post_type');
    $user = $this->getUser();
    $trans_tbl = DomusSearchRoute::$translit_table;
    $this->theme = $request->getParameter('theme');
    $post_theme = str_replace(array_values($trans_tbl), array_keys($trans_tbl), $request->getParameter('theme'));
    $this->other_posts = Post::getOtherPostTypes($post_type);
    $this->cache_prefix = sprintf('%s_%d_%d_%d',
                                  $post_type,
                                  $request->getCookie('js_on'),
                                  $this->getUser()->current_region->id,
                                  sfConfig::get('is_new_building'));

    $this->post_theme = Doctrine::getTable('Theme')->createQuery()
      ->select('title, id')
      ->andWhere('title = ?', $post_theme)
      ->fetchOne();

    $this->current_theme = $this->post_theme->id;
    $this->post_type = $post_type;
    $this->forward404Unless($this->post_theme);

    $query = Doctrine:: getTable('Post')->createQuery('p')
      ->select('p.title, p.lid, p.created_at')
      ->leftJoin('p.PostRegion r')
      ->leftJoin('p.PostTheme t')
      ->andWhere(PostQuery::getWhereExpression('t.theme_id'))
      ->andWhere('p.post_type = ?', $post_type)
      ->andWhere('p.status = ?', 'publish')
      ->andWhere('r.region_id = ?', $user->current_region->id)
      ->andWhere('t.theme_id = ?', $this->post_theme->id)
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
      ->orderBy('p.created_at desc');

    $this->pager = new sfDoctrinePager('Post', sfConfig::get('app_posts_my_max_per_page', 10));
    $this->pager->setQuery($query);
    $this->pager->setPage($request->getParameter('page', 1));
    $this->pager->init();

    $this->setLayout('homepage');

  }
}
