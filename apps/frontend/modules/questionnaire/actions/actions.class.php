<?php

/**
 * questionnaire actions.
 *
 * @package    domus
 * @subpackage questionnaire
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class questionnaireActions extends sfActions
{
  
  public function postExecute() {
    MetaParse::setMetas($this);
  }

  /**
   * Executes index action
   *
   * @param sfRequest $request A request object
   */
  public function executeIndex(sfWebRequest $request) {
    $user = $this->getUser();
    $active_questionnaires = Doctrine::getTable('Post')->getActiveQuestionnaires();

    $this->voted_questionnaires = array();
    $this->not_voted_questionnaires = array();

    $inactive_questionnaires_query = Doctrine::getTable('Post')->getInactiveQuestionnairesQuery();
    $this->pager = new sfDoctrinePager('Post', 10);
    $this->pager->setQuery($inactive_questionnaires_query);
    $this->pager->setPage($request->getParameter('page', 1));
    $this->pager->init();

    $uv = $request->getCookie('questionnaire_votes', false);
    $uv = $uv ? unserialize(base64_decode($uv)) : array();
    
    foreach ($active_questionnaires as $q) {
      if (isset($uv[$q->id]))
        $this->voted_questionnaires[] = $q;
      else
        $this->not_voted_questionnaires[] = $q;
    }

    $this->cache_prefix = sprintf(
        '%d_%d_',
        $request->getCookie('js_on'),
        $this->getUser()->current_region->id
    );
    $this->setLayout('homepage');
  }

  public function executeVote(sfWebRequest $request) {
    $this->forward404Unless($request->isXmlHttpRequest());

    $questionnaire = Doctrine::getTable('Post')->getActiveQuestionnaire($request->getParameter('id'));
    $answer = Doctrine::getTable('QuestionnaireAnswers')->getAnswer($questionnaire->id, (int) $request->getPostParameter('answer'));

    if (!$questionnaire || !$answer)
      return null;
    $user = $this->getUser();
    
    $uv = $request->getCookie('questionnaire_votes', false);
    $uv = $uv ? unserialize(base64_decode($uv)) : array();

    if ($user->isAuthenticated()) {
        $vote = new QuestionnaireAnswersVote(null, true);
        $vote->user_id = $user->id;
        $vote->post_id = $questionnaire->id;
        $vote->answer_id = $answer->id;
        $vote->save();
    }

    if (!isset($uv[$request->getParameter('id')])) {
      $answer->vote++;
      $answer->save();

      $uv[$request->getParameter('id')] = (int) $request->getPostParameter('answer');
      sfContext::getInstance()->getResponse()->setCookie('questionnaire_votes', base64_encode(serialize($uv)), time()+60*60*24*30);
    }

    return $this->renderPartial('questionnaire/questionnaire_voted', array('questionnaire' => $questionnaire));
  }

  public function executeDeleteanswer(sfWebRequest $request) {
    $this->forward404Unless($request->isXmlHttpRequest());

    $user = $this->getUser();
    $this->forward404Unless($user->hasCredential('redactor-qa-delete'));

    $this->forward404Unless($answer = Doctrine::getTable('QuestionnaireAnswers')->find($request->getPostParameter('answer_id')));
    $answer->delete();
    return $this->renderText(json_encode(array('delete' => 'ok')));
  }

  public function executeAddanswer(sfWebRequest $request) {
    $this->forward404Unless($request->isXmlHttpRequest());

    $user = $this->getUser();
    $this->forward404Unless($user->hasCredential('redactor-qa-actions'));

    $this->forward404Unless($q = Doctrine::getTable('Post')->find($request->getPostParameter('q_id')));
    $answer = new QuestionnaireAnswers();

    $q->Answers[] = $answer;
    $q->save();
    return $this->renderPartial('add_answer', array('id' => $answer->id));
  }

}
