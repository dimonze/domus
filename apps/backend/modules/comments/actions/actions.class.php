<?php

require_once dirname(__FILE__).'/../lib/commentsGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/commentsGeneratorHelper.class.php';

/**
 * comments actions.
 *
 * @package    domus
 * @subpackage comments
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12474 2008-10-31 10:41:27Z fabien $
 */
class commentsActions extends autoCommentsActions
{
  protected function buildQuery() {
    return parent::buildQuery()
      ->leftJoin('a.Post p')
      ->andWhereIn('p.post_type', array('news', 'author_article', 'expert_article', 'events', 'article', 'analytics'))
      ->andWhere('a.body != ?', '');
  }
  
  public function executeListCommentDelete(sfWebRequest $request) {
    $comment = $this->getRoute()->getObject();
    $this->forward404Unless($comment);

    $comment->deleted = 1;
    $comment->save();
    $this->redirect($request->getReferer());
  }
  public function executeListCommentKill(sfWebRequest $request) {
    Doctrine::getTable('PostComment')->killComment((int)$request->getParameter('id'));
    $this->redirect($request->getReferer());
  }
}
