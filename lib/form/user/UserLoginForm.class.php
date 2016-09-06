<?php

/**
 * UserLoginForm
 *
 * @package    domus
 * @subpackage forms
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class UserLoginForm extends BaseForm implements AjaxFormInterface
{
  public function configure() {
    $this->setWidgets(array(
      'email'    => new sfWidgetFormInputText(),
      'password' => new sfWidgetFormInputPassword(),
      'remember' => new sfWidgetFormInputCheckbox()
    ));

    $this->setValidators(array(
      'email'    => new sfValidatorEmail(array('required' => true)),
      'password' => new sfValidatorString(array('required' => true)),
      'remember' => new sfValidatorBoolean(array('required' => false))
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorCallback(array('callback' => array($this, 'tryLogin'), 'required' => true))
    );
    $this->validatorSchema->setOption('allow_extra_fields', true);
    $this->validatorSchema->setOption('filter_extra_fields', true);

    $this->widgetSchema->setNameFormat('user[%s]');


    AjaxForm::setErrorMessages($this);
  }

  public function getErrorsArray()
  {
    return AjaxForm::getErrorsArray($this);
  }
  
  public function tryLogin($validator, $values) {
    if (!empty($values['email']) && !empty($values['password']))
    {
      if (!sfContext::getInstance()->getUser()->processLogin($values)) {
        throw new sfValidatorErrorSchema(
          $validator,
          array('password' => new sfValidatorError($validator, 'password_email_match')));
      }
    }
    return $values;
  }

  public function getObject() {
    $values = $this->getValues();

    if (!$this->isBound() || !$this->isValid()) {
      return null;
    }
    $user = Doctrine::getTable('User')->createQuery()
      ->andWhere('email = ?', $values['email'])
      ->fetchOne();
    if ($user){
      return $user;
    }
    else {
      return false;
    }
  }
}