<?php

/**
 * Notification form.
 *
 * @package    form
 * @subpackage Notification
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class NotificationForm extends BaseNotificationForm implements AjaxFormInterface
{
  public function configure()
  {
    $this->widgetSchema['email'] = new sfWidgetFormInputText();
    $this->widgetSchema['period'] = new sfWidgetFormChoice(array('choices' => array('daily' => 'ежедневно', 'weekly' => 'еженедельно', 'monthly' => 'ежемесячно')));

    $this->setValidators(array(
      'email'  => new sfValidatorEmail(array('required' => true)),
      'period' => new sfValidatorChoice(array('choices' => array('daily' => 'daily', 'weekly' => 'weekly', 'monthly' => 'monthly'))),
      'model' => new sfValidatorString(array('required' => true)),
      'field' => new sfValidatorString(array('required' => false)),
      'pk' => new sfValidatorString(array('required' => true))
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorCallback(array('callback' => array($this, 'checkSubscriber'), 'required' => true))
    );

    $this->widgetSchema->setNameFormat('%s');
    AjaxForm::setErrorMessages($this);
  }

  public function getErrorsArray()
  {
    return AjaxForm::getErrorsArray($this);
  }


  public function checkSubscriber($validator, $values) {
    if (!empty($values['email']) && !empty($values['model']) && !empty($values['pk'])) {
      $q = Doctrine::getTable('Notification')->createQuery()
            ->andWhere('email = ?', $values['email'])
            ->andWhere('model = ?', $values['model'])
            ->andWhere('field = ?', $values['field'])
            ->andWhere('pk = ?', $values['pk']);
      if ($q->count() > 0)
      {
        throw new sfValidatorErrorSchema(
          $validator,
          array('email' => new sfValidatorError($validator, 'email_already_subscribed')));
      }
    }
    return $values;
  }
}