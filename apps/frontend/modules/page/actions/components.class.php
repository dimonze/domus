<?php

/**
 * page components.
 *
 * @package    domus
 * @subpackage page
 * @author     Garin Studio
 * @version    SVN: $Id: components.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class pageComponents extends sfComponents
{
  public function executeSubmenu() {
    $page = sfContext::getInstance()->getRequest()->getParameter('current_page');

    if (!$page)
    {
      return sfView::NONE;
    }

    $query = Doctrine::getTable('Page')->createQuery();
    if ($page->parent_id) {
      $query->andWhere('parent_id = ?', $page->parent_id);
      $this->parent = $page->Page;
    }
    else {
      $query->andWhere('parent_id = ?', $page->id);
      $this->parent = $page;
    }
    $query->andWhere('in_menu = ?', true);

    $this->items = $query->execute();
  }

  public function executeAside(sfWebRequest $request) {
    $this->cache_prefix = sprintf(
      '%d_%d_%d_',
      $request->getCookie('js_on'),
      $this->getUser()->current_region->id,
      sfConfig::get('is_new_building')
    );
    
    $context = sfContext::getInstance();
    require $context->getConfigCache()->checkConfig('config/aside.yml');
    $this->aside = sfConfig::get(sprintf('aside_%s_%s', $context->getModuleName(), $context->getActionName()));
    $this->module = $context->getModuleName();
    $this->action = $context->getActionName();
  }

  public function executeThemeList() {
     $this->themes = Theme::getThemes();
     $this->blue_bg = true;
     $this->url_prefix = '';

     switch ($this->type) {
       case 'qa':
         $this->route = 'qa_by_theme';
         break;

       case 'author_article':
         $this->route = 'author_article_by_theme';
         $this->blue_bg = false;
         break;
       
       case 'expert_article':
         $this->route = 'expert_article_by_theme';
         $this->blue_bg = false;
         break;

       case 'analytics':
       case 'events':
       case 'article':
         $this->route = 'posts_by_theme';
         $this->url_prefix = '&post_type='.$this->type.'&page=1';
         break;

       case 'news':
         $this->route = 'news_by_theme';
         $this->url_prefix = '&news_section=' . $this->news_section . '&created_at=' . date('Y-m-d');
     }

  }
}
