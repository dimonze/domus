<?php

/**
 * User form.
 *
 * @package    form
 * @subpackage User
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class UserForm extends BaseUserForm
{
  public function configure() {
    $this->widgetSchema['type'] = new sfWidgetFormChoice(array('choices' => User::$types));
    $this->widgetSchema['employer_id'] = new sfWidgetFormInputText();

    $this->validatorSchema['email'] = new sfValidatorAnd(array(
      new sfValidatorEmail(array('required' => true)),
      new sfValidatorDoctrineUnique(
        array(
          'model'     => 'User',
          'column'    => 'email'
        ),
        array(
          'invalid'   => 'Пользователь с этим адресом уже зарегистрирован'
        )
      )
    ));

    if (!sfContext::getInstance()->getUser()->hasCredential('admin-user-group')) {
      unset($this['group_id']);
    }

    unset($this['remember_key'], $this['created_at'], $this['last_login'],
          $this['remember_till'], $this['deleted'], $this['rating']);
  }
}
