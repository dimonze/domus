<?php

/**
 * Notification filter form.
 *
 * @package    filters
 * @subpackage Notification *
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class NotificationFormFilter extends BaseNotificationFormFilter
{
  public function configure()
  {
    $this->widgetSchema['email'] = new sfWidgetFormFilterInput();
    $this->widgetSchema['model'] = new sfWidgetFormChoice(array('choices' => array('' => '') + Notification::$models));
    $this->widgetSchema['period'] = new sfWidgetFormChoice(array('choices' => array('' => '') + Notification::$periods));

    $this->validatorSchema['email'] = new sfValidatorPass(array('required' => false));
    $this->validatorSchema['model'] = new sfValidatorChoice(array('required' => false, 'choices' => array_keys(Notification::$models)));
    $this->validatorSchema['period'] = new sfValidatorChoice(array('required' => false, 'choices' => array_keys(Notification::$periods)));
  }

  public function getFields()
  {
    return array(
      'email'  => 'Text',
      'model'  => 'Enum',
      'period' => 'Enum',
    );
  }
}