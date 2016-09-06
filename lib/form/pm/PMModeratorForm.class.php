<?php

/**
 * PMModerator form.
 *
 * @package    form
 */
class PMModeratorForm extends PMForm implements AjaxFormInterface
{
  public function configure()
  {
   $this->setWidgets(array(
      'receiver'   => new sfWidgetFormInputText(),
      'priority'   => $this->_prioritySelect,
      'subject'    => new sfWidgetFormInputText(),
      'message'    => new sfWidgetFormTextarea(),
      'lot_id'     => new sfWidgetFormInputText(array(), array('type' => 'hidden'))
    ));

    $this->setValidators(array(
      'receiver'  => new sfValidatorAnd(array(
          new sfValidatorEmail(),
          new sfValidatorDoctrineChoice(array('model' => 'User', 'column' => 'email')),
        )),
      'priority'  => new sfValidatorStringPMNoLogin(array('required' => false)),
      'subject'   => new sfValidatorString(),
      'message'   => new sfValidatorString(),
      'lot_id'    => new sfValidatorPass(),
    ));

    $this->widgetSchema->setNameFormat('pm[%s]');
    AjaxForm::setErrorMessages($this);
  }
}