<?php

/**
 * questionaire components.
 *
 * @package    domus
 * @subpackage news
 * @author     Garin Studio
 */
class questionnaireComponents extends sfComponents
{
  public function executeAsideone() {
    $this->getResponse()->addJavascript('news_portal.js');
    $user = $this->getUser();
    $active_questionnaires = Doctrine::getTable('Post')->getActiveQuestionnaires();
    if(!count($active_questionnaires)) return false;
    
    $request = sfContext::getInstance()->getRequest();
    $uv = $request->getCookie('questionnaire_votes', false);
    $uv = $uv ? unserialize(base64_decode($uv)) : array();
    
    foreach($active_questionnaires as $q) {
      if(isset($uv[$q->id])) $voted_questionnaires[] = $q;
      else $not_voted_questionnaires[] = $q;
    }

    if(isset($not_voted_questionnaires)) {
      $this->not_voted = true;
      $this->questionnaire = $not_voted_questionnaires[array_rand($not_voted_questionnaires)];
    }
    else {
      $this->not_voted = false;
      $this->questionnaire = $voted_questionnaires[array_rand($voted_questionnaires)];
    }
  }
}
