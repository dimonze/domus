<?php

/**
 * PM form.
 *
 * @package    form
 */
class PMForm extends BaseForm implements AjaxFormInterface
{
  protected $_object;
  protected $_prioritySelect;

  public function configure()
  {
    $this->setWidgets(array(
      'receiver'   => new sfWidgetFormInputText(),
      'subject'    => new sfWidgetFormInputText(),
      'message'    => new sfWidgetFormTextarea(),
      'priority'   => $this->_prioritySelect,
    ));

    $this->setValidators(array(
      'receiver'  => new sfValidatorAnd(array(
          new sfValidatorEmail(),
          new sfValidatorDoctrineChoice(array('model' => 'User', 'column' => 'email')),
        )),
      'subject'   => new sfValidatorString(),
      'message'   => new sfValidatorString(),
      'priority'  => new sfValidatorStringPMNoLogin(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('pm[%s]');
    AjaxForm::setErrorMessages($this);
  }

  public function getErrorsArray()
  {
    return AjaxForm::getErrorsArray($this);
  }

  public function bind(array $taintedValues = null, array $taintedFiles = null)
  {
    parent::bind($taintedValues, $taintedFiles);
    $this->_object = new PM();
  }

  /**
   * @param mixed $sender_id null|false|integer
   * @return integer pm->id
   */
  public function save($sender_id = null)
  {
    $this->updateObject($sender_id);
    $this->_object->save();
    return $this->_object->id;
  }

  /**
   * @param mixed $sender_id null|false|integer
   * @return PM
   */
  public function getObject($sender_id = null)
  {
    $this->updateObject($sender_id);
    return $this->_object;
  }

  /**
   * @param mixed $sender_id null|false|integer
   * @return void
   */
  public function updateObject($sender_id = null)
  {
    if (null === $sender_id) {
      $sender_id = sfContext::getInstance()->getUser()->id;
    }
    elseif ($sender_id) {
      $sender_id = $sender_id;
    }
    else {
      $sender_id = null;
    }

    $this->_object->fromArray(array(
      'sender'   => $sender_id,
      'receiver' => Doctrine::getTable('User')->findOneByEmail($this->getValue('receiver'))->id,
      'subject'  => $this->getValue('subject'),
      'message'  => $this->getValue('message'),
      'red'      => false,
      'sent_at'  => date('Y-m-d H:i:s'),
      'priority' => $this->getValue('priority'),
    ));
  }

  public function __construct($defaults = array(), $options = array(), $CSRFSecret = null) {
    $this->_prioritySelect = new sfWidgetFormSelect(
      array(
        'choices' =>
          array(
            'none' => '',
            'low'  => 'Низкая',
            'mid'  => 'Средняя',
            'high' => 'Высокая'
          ),
      )
    );
    parent::__construct($defaults, $options, $CSRFSecret);
  }

}