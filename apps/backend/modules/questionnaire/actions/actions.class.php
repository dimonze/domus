<?php

require_once dirname(__FILE__) . '/../lib/questionnaireGeneratorConfiguration.class.php';
require_once dirname(__FILE__) . '/../lib/questionnaireGeneratorHelper.class.php';

/**
 * questionnaire actions.
 *
 * @package    domus
 * @subpackage questionnaire
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12474 2008-10-31 10:41:27Z fabien $
 */
class questionnaireActions extends autoQuestionnaireActions
{
  protected function buildQuery() {
    return parent::buildQuery()->andWhere('post_type = ?', 'questionnaire');
  }
  public function executeUpdate(sfWebRequest $request) {
    $this->forward404Unless($request->isMethod('post') || $request->isMethod('put'));
    $this->post = $this->getRoute()->getObject();
    $data = $request->getParameter('questionnaire');

    $submit = $request->hasParameter('submit') ? $request->getPostParameter('submit') : false;
    $submit = explode('_', $submit);
    switch ($submit[0]) {
      case 'удалить':
        $this->forward404Unless($answer = Doctrine::getTable('QuestionnaireAnswers')
            ->find($submit[1]));
        unset($data['answer_' . $submit[1]]);
        $answer->delete();
        break;
      case 'добавить':
        $this->post['Answers'][] = new QuestionnaireAnswers();
        break;
    }

    $this->form = new QuestionnaireForm($this->post);

    $this->form->bind($data);
    if ($this->form->isValid()) {
      $questionnaire = $this->form->save();
      $this->getUser()->setFlash('notice', 'Успешно сохранено');
    }
    else {
      $this->getUser()->setFlash('notice', 'что то пошло не так');
    }
    $this->redirect(array('sf_route' => 'questionnaire_edit', 'sf_subject' => $this->post));
    $this->setTemplate('edit');
  }

  public function executeDelete(sfWebRequest $request) {
    $q = $this->getRoute()->getObject();
    foreach($q->Answers as $answer) {
      $answer->delete();
    }
    parent::executeDelete($request);
  }
}
