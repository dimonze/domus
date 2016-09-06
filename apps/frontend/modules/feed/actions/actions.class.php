<?php

/**
 * feed actions.
 *
 * @package    domus
 * @subpackage feed
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class feedActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->forward404();
  }

  public function executeFeed(sfWebRequest $request)
  {
    $this->forward404Unless(in_array($request->getParameter('sf_format'), array('rss', 'xml')));
    $this->forward404Unless($request->hasParameter('partner'));
    $partner = $request->getParameter('partner');

    $news = Doctrine::getTable('Post')->createQueryActive('p')
      ->andWhere('p.post_type = ?', 'news')
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
      ->orderBy('p.created_at desc')
      ->limit(20);

    $articles = Doctrine::getTable('Post')->createQueryActive('p')
      ->andWhere('p.post_type = ?', 'article')
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
      ->orderBy('p.created_at desc')
      ->limit(20);

    $events = Doctrine::getTable('Post')->createQueryActive('p')
      ->andWhere('p.post_type = ?', 'events')
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
      ->orderBy('p.created_at desc')
      ->limit(20);

    $analytics = Doctrine::getTable('Post')->createQueryActive('p')
      ->andWhere('p.post_type = ?', 'analytics')
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
      ->orderBy('p.created_at desc')
      ->limit(20);

    $author_articles = Doctrine::getTable('Post')->createQueryActive('p')
      ->andWhere('p.post_type = ?', 'author_article')
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
      ->orderBy('p.created_at desc')
      ->limit(20);

    $expert_articles = Doctrine::getTable('Post')->createQueryActive('p')
      ->andWhere('p.post_type = ?', 'expert_article')
      ->andWhere('p.created_at <= ?', date('Y-m-d H:i:s'))
      ->orderBy('p.created_at desc')
      ->limit(20);

    switch($partner) {
      case 'yandex':
      case 'rscontext':
      case 'rscontext1':
      case 'rscontext2':
        $this->forward404If($request->getParameter('sf_format') == 'xml');
        $this->news = $news->andWhere('p.in_yandex_rss = ?', 1)->execute();
        $this->articles = $articles->andWhere('p.in_yandex_rss = ?', 1)->execute();
        $this->events = $events->andWhere('p.in_yandex_rss = ?', 1)->execute();
        $this->analytics = $analytics->andWhere('p.in_yandex_rss = ?', 1)->execute();
        $this->author_articles = $author_articles->andWhere('p.in_yandex_rss = ?', 1)->execute();
        $this->expert_articles = $expert_articles->andWhere('p.in_yandex_rss = ?', 1)->execute();
        $this->setTemplate('yandex', 'feed');
        break;
      case 'google':
        $this->forward404If($request->getParameter('sf_format') == 'rss');
        $this->news = $news->andWhere('p.in_google_xml = ?', 1)->execute();
        $this->articles = $articles->andWhere('p.in_google_xml = ?', 1)->execute();
        $this->events = $events->andWhere('p.in_google_xml = ?', 1)->execute();
        $this->analytics = $analytics->andWhere('p.in_google_xml = ?', 1)->execute();
        $this->author_articles = $author_articles->andWhere('p.in_google_xml = ?', 1)->execute();
        $this->expert_articles = $expert_articles->andWhere('p.in_google_xml = ?', 1)->execute();
        $this->setTemplate('google', 'feed');
        break;
      case 'rambler':
        $this->forward404If($request->getParameter('sf_format') == 'xml');
        $this->news = $news->andWhere('p.in_rambler_rss = ?', 1)->execute();
        $this->articles = $articles->andWhere('p.in_rambler_rss = ?', 1)->execute();
        $this->events = $events->andWhere('p.in_rambler_rss = ?', 1)->execute();
        $this->analytics = $analytics->andWhere('p.in_rambler_rss = ?', 1)->execute();
        $this->author_articles = $author_articles->andWhere('p.in_rambler_rss = ?', 1)->execute();
        $this->expert_articles = $expert_articles->andWhere('p.in_rambler_rss = ?', 1)->execute();
        $this->setTemplate('rambler', 'feed');
        break;
    }

    switch ($partner) {
      case 'yandex':
        break;
      case 'rscontext':
        $this->proxy_server = 'http://context.mesto.ru/?url=';
        break;
      case 'rscontext1':
        $this->proxy_server = 'http://context.mesto.ru/?url=';
        break;
      case 'rscontext2':
        $this->proxy_server = 'http://context.mesto.ru/?url=';
        break;
      case 'google':
        break;
      case 'rambler':
        break;
    }
    $this->setLayout(false);
  }
}
