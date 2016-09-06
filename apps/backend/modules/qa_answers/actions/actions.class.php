<?php

require_once dirname(__FILE__).'/../lib/qa_answersGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/qa_answersGeneratorHelper.class.php';

/**
 * qa_answers actions.
 *
 * @package    domus
 * @subpackage qa_answers
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12474 2008-10-31 10:41:27Z fabien $
 */
class qa_answersActions extends autoQa_answersActions
{
  protected function buildQuery() {
    return parent::buildQuery()
      ->leftJoin('a.Post p')
      ->andWhereIn('p.post_type', array('qa'))
      ->andWhere('a.body != ?', '');
  }
  
  public function executeListCommentDelete(sfWebRequest $request) {
    $comment = $this->getRoute()->getObject();
    $this->forward404Unless($comment);

    $comment->deleted = 1;
    $comment->save();
    $this->redirect($request->getReferer());
  }
}
