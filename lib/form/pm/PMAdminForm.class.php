<?php

/**
 * PMAdmin form.
 *
 * @package    form
 */
class PMAdminForm extends PMForm implements AjaxFormInterface
{
  public function configure()
  {
   $this->setWidgets(array(
      'receiver'   => new sfWidgetFormInputText(),
      'priority'   => $this->_prioritySelect,
      'subject'    => new sfWidgetFormInputText(),
      'message'    => new sfWidgetFormTextarea()
    ));

    $this->setValidators(array(
      'receiver'  => new sfValidatorString(),
      'subject'   => new sfValidatorString(),
      'message'   => new sfValidatorString(),
      'priority'  => new sfValidatorStringPMNoLogin(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('pm[%s]');
    AjaxForm::setErrorMessages($this);
  }
}