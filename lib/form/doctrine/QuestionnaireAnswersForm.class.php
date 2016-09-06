<?php

/**
 * QuestionnaireAnswers form.
 *
 * @package    form
 * @subpackage QuestionnaireAnswers
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class QuestionnaireAnswersForm extends BaseQuestionnaireAnswersForm
{

  public function configure() {
    $this->widgetSchema['post_id'] = new sfWidgetFormDoctrineChoice(
        array(
          'model' => 'Post',
          'table_method' => 'getAllQuestionnaires'
      ));

    $this->validatorSchema->setPostValidator(new sfValidatorPass());
    $this->validatorSchema['title'] = new sfValidatorPass();
    $this->widgetSchema['vote'] = new sfWidgetFormInputText(array(
        'default' => 0
      ));
    $this->validatorSchema['vote'] = new sfValidatorInteger(array(
        'required' => false,
        ), array(
        'invalid' => 'введите только цифры'
      ));
    $this->widgetSchema['vote'] = new sfWidgetFormInputText(array(
        'default' => 0
      ));

    $this->widgetSchema->setLabels(array(
      'title' => 'Ответ',
      'vote' => 'Число голосов'
    ));
  }

}