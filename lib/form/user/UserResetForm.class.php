<?php

/**
 * UserResetForm
 *
 * @package    domus
 * @subpackage forms
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class UserResetForm extends BaseForm implements AjaxFormInterface
{
  public function configure() {
    $this->setWidgets(array(
      'email'    => new sfWidgetFormInputText()
    ));

    $this->setValidators(array(
      'email'    => new sfValidatorAnd(array(
          new sfValidatorEmail(array('required' => true)),
          new sfValidatorDoctrineChoice(array('model' => 'User', 'column' => array('email')))
      ))
    ));

    $this->widgetSchema->setNameFormat('user[%s]');
    AjaxForm::setErrorMessages($this);
  }

  public function getErrorsArray()
  {
    return AjaxForm::getErrorsArray($this);
  }


}