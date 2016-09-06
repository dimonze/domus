<?php

/**
 * news_portal actions.
 *
 * @package    domus
 * @subpackage news_portal
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class news_portalActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
  }

  public function executeBlog(sfWebRequest $request)
  {
    $this->forward('blog', 'index');
  }

  public function executePostauthor(sfWebRequest $request)
  {
    $this->forward('post_author', 'index');
  }

  public function executeAuthorarticle(sfWebRequest $request)
  {
    $this->forward('author_article', 'index');
  }

  public function executeExpertarticle(sfWebRequest $request)
  {
    $this->forward('expert_article', 'index');
  }

  public function executeNews(sfWebRequest $request)
  {
    $this->forward('news', 'index');
  }

  public function executeEvents(sfWebRequest $request)
  {
    $this->forward('events', 'index');
  }

  public function executeArticle(sfWebRequest $request)
  {
    $this->forward('article', 'index');
  }

  public function executeAnalytics(sfWebRequest $request)
  {
    $this->forward('analytics', 'index');
  }

  public function executeThemes(sfWebRequest $request)
  {
    $this->forward('post_themes', 'index');
  }
  public function executeQa(sfWebRequest $request)
  {
    $this->forward('qa', 'index');
  }
  public function executeQa_answers(sfWebRequest $request)
  {
    $this->forward('qa_answers', 'index');
  }
  public function executeComments(sfWebRequest $request)
  {
    $this->forward('comments', 'index');
  }
  public function executeQuestionnaire(sfWebRequest $request)
  {
    $this->forward('questionnaire', 'index');
  }
}
