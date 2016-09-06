<?php

/**
 * PMNoLoginForm form.
 *
 * @package    form
 */
class PMNoLoginForm extends PMForm
{
  public function configure()
  {
   $this->setWidgets(array(
      'receiver'   => new sfWidgetFormInputText(),
      'subject'    => new sfWidgetFormInputText(),
      'message'    => new sfWidgetFormTextarea(),
      'name'       => new sfWidgetFormInputText(),
      'email'      => new sfWidgetFormInputText(),
      'phone'      => new sfWidgetFormInputText(),
      'priority'   => $this->_prioritySelect,
    ));

    $this->setValidators(array(
      'receiver'  => new sfValidatorAnd(array(
          new sfValidatorEmail(),
          new sfValidatorDoctrineChoice(array('model' => 'User', 'column' => 'email')),
        )),
      'subject'   => new sfValidatorStringPMNoLogin(),
      'message'   => new sfValidatorStringPMNoLogin(),
      'name'      => new sfValidatorStringPMNoLogin(),
      'email'     => new sfValidatorEmail(),
      'phone'     => new sfValidatorStringPMNoLogin(array('required' => false)),
      'priority'  => new sfValidatorStringPMNoLogin(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('pm[%s]');
    AjaxForm::setErrorMessages($this);
  }

  /**
   * @param mixed $sender_id null|false|integer
   * @return void
   */
  public function updateObject($sender_id = null)
  {
    if (!$this->_object) {
      $this->_object = new PM();
    }

    $message = sprintf("От : %s\nEmail: %s\n", $this->getValue('name'), $this->getValue('email'));
    $phone = $this->getValue('phone');
    if (!empty($phone)){
      $message .= 'Телефон: ' . $phone . "\n";
    }
    $message .= "\n" . $this->getValue('message');

    $this->_object->fromArray(array(
      'sender'   => null,
      'receiver' => Doctrine::getTable('User')->findOneByEmail($this->getValue('receiver'))->id,
      'subject'  => $this->getValue('subject'),
      'message'  => $message,
      'red'      => false,
      'sent_at'  => date('Y-m-d H:i:s'),
      'priority' => $this->getValue('priority'),
    ));
  }
}