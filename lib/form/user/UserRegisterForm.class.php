<?php

/**
 * UserRegisterForm
 *
 * @package    domus
 * @subpackage forms
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class UserRegisterForm extends BaseForm implements AjaxFormInterface
{
  public function configure ()
  {
    $user_types = User::$types;
    unset($user_types['source']);
    $this->setWidgets(array(
      'type'                 => new sfWidgetFormSelect(array('choices' => $user_types)),
      'company_name'         => new sfWidgetFormInputText(),
      'invite'               => new sfWidgetFormInputText(),
      'name'                 => new sfWidgetFormInputText(),
      'country_code'         => new sfWidgetFormInputText(array('default' => '+7'), array('class' => 'country-code')),
      'area_code'            => new sfWidgetFormInputText(array('default' => 'код'), array('class' => 'area-code')),
      'phone'                => new sfWidgetFormInputText(array('default' => 'номер'), array('class' => 'phone')),
      'email'                => new sfWidgetFormInputText(),
      'password'             => new sfWidgetFormInputPassword(),
      'password_again'       => new sfWidgetFormInputPassword(),
      'captcha'              => new sfWidgetFormInputKCaptcha(array(), array('class' => 'captcha'))
    ));

    $this->setValidators(array(
      'type'                 => new sfValidatorChoice(array('choices' => array_keys($user_types))),
      'company_name'         => new sfValidatorAnd(array(
        new sfValidatorString(array('required' => true)),
        new sfValidatorDoctrineUniqueDeletedToo(
          array(
            'model'     => 'User',
            'column'    => 'company_name',
          ),
          array(
            'invalid'   => 'Эта организация уже зарегистрирована'
          ))
        )),
      'invite'               => new sfValidatorString(array('required' => true)),
      'name'                 => new sfValidatorString(array('required' => true)),
      'country_code'         => new sfValidatorRegex(array('pattern' => '/^\+\d+$/', 'required' => true)),
      'area_code'            => new sfValidatorNumber(array('required' => true)),
      'phone'                => new sfValidatorCleanNumber(array('required' => true)),
      'email'                => new sfValidatorAnd(array(
        new sfValidatorEmail(array('required' => true)),
        new sfValidatorDoctrineUnique(array(
          'model'     => 'User',
          'column'    => 'email'
        ), array(
          'invalid'   => 'Пользователь с этим адресом уже зарегистрирован'
        ))
      )),
      'password'             => new sfValidatorString(array('required' => true, 'min_length' => 6)),
      'password_again'       => new sfValidatorString(array('required' => true)),
      'captcha'              => new sfValidatorKCaptcha(),
    ));

    $this->validatorSchema->setPostValidator(new sfValidatorAnd(array(
        new sfValidatorSchemaCompare('password', '==', 'password_again'),
        new sfValidatorCallback(array('callback' => array($this, 'validatePhone'), 'required' => true)),
        new sfValidatorCallback(array('callback' => array($this, 'validateInvite'), 'required' => true)),
      ))
    );

    $this->widgetSchema->setNameFormat('user[%s]');
    AjaxForm::setErrorMessages($this);
  }

  public function getErrorsArray ()
  {
    return AjaxForm::getErrorsArray($this);
  }

  public function getObject()
  {
    if (!$this->isBound() || !$this->isValid()) {
      return null;
    }

    $values = $this->getValues();

    if (!empty($values['invite'])) {
      $invite = Doctrine::getTable('Invite')->createQuery()
          ->andWhere('email = ?', $values['email'])
          ->andWhere('code = ?', $values['invite'])
          ->fetchOne();
    }

    $user = new User();
    $user->fromArray(array(
      'type'         => $values['type'],
      'name'         => $values['name'],
      'email'        => $values['email'],
      'password'     => $values['password'],
      'phone'        => Toolkit::formatPhoneNumber($values['country_code'],
                                                   $values['area_code'],
                                                   $values['phone']),
      'company_name' => empty($values['company_name']) ? null : $values['company_name'],
    ));

    if (!empty($invite)) {
      $user->Employer = $invite->User;
    }

    return $user;
  }

  public function changeWidgets ($type = null)
  {
    switch ($type) {
      case 'company':
        unset($this['invite']);
        break;

      case 'employee':
        unset($this['company_name']);
        break;

      default:
        unset($this['invite'], $this['company_name']);
        break;
    }

    if ($type == 'employee') {
      $this->validatorSchema->setPostValidator(new sfValidatorAnd(array(
        new sfValidatorSchemaCompare('password', '==', 'password_again'),
        new sfValidatorCallback(array('callback' => array($this, 'validatePhone'), 'required' => true)),
        new sfValidatorCallback(array('callback' => array($this, 'validateInvite'), 'required' => true)),
      )));
    }
    else {
      $this->validatorSchema->setPostValidator(new sfValidatorAnd(array(
        new sfValidatorSchemaCompare('password', '==', 'password_again'),
        new sfValidatorCallback(array('callback' => array($this, 'validatePhone'), 'required' => true)),
      )));
    }

    AjaxForm::setErrorMessages($this);
  }

  public function validatePhone ($validator_callback, $values)
  {
    $phone = Toolkit::formatPhoneNumber($values['country_code'], $values['area_code'], $values['phone']);

    // format validator
    if (null === $phone) {
      throw new sfValidatorErrorSchema($validator_callback, array(
        'phone' => new sfValidatorError($validator_callback, 'invalid')
      ));
    }

    // length validator
    if (strlen($phone) > 18) {
      throw new sfValidatorErrorSchema($validator_callback, array(
        'phone' => new sfValidatorError($validator_callback, 'invalid')
      ));
    }

    // unique validator
    $validator = new sfValidatorDoctrineUnique(array('model' => 'User', 'column' => 'phone'));
    try {
      $validator->clean($phone);
    }
    catch(Exception $e) {
      throw new sfValidatorErrorSchema($validator, array(
        'phone' => new sfValidatorError($validator, 'duplicate_phone')
      ));
    }
    return $values;
  }

  public function validateInvite ($validator, $values)
  {
    if (empty($values['invite'])) {
      throw new sfValidatorError($validator, 'invite cannot be blank');
    }

    $query = Doctrine::getTable('Invite')->createQuery()
        ->andWhere('email = ?', $values['email'])
        ->andWhere('code = ?', $values['invite']);

    if ($query->count() != 1) {
      throw new sfValidatorErrorSchema($validator, array(
        'invite' => new sfValidatorError($validator, 'invalid')
      ));
    }

    return $values;
  }
}
