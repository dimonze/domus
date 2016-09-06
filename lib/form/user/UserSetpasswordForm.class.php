<?php

/**
 * UserSetPasswordForm
 *
 * @package    domus
 * @subpackage forms
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class UserSetPasswordForm extends BaseForm
{
  public function configure() {
    $this->setWidgets(array(
      'password'       => new sfWidgetFormInputPassword(),
      'password_again' => new sfWidgetFormInputPassword()
    ));

    $this->setValidators(array(
      'password'       => new sfValidatorString(),
      'password_again' => new sfValidatorString(),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorSchemaCompare('password', '==', 'password_again')
    );

    $this->widgetSchema->setNameFormat('user[%s]');
    AjaxForm::setErrorMessages($this);
  }
}