<?php

/**
 * UserInfo form.
 *
 * @package    form
 * @subpackage UserInfo
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class UserInfoForm extends BaseUserInfoForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'country'          => new sfWidgetFormInputText(array('default' => '+7'), array('class' => 'country-code')),
      'area'             => new sfWidgetFormInputText(array('default' => 'код'), array('class' => 'area-code')),
      'number'           => new sfWidgetFormInputText(array('default' => 'номер'), array('class' => 'phone')),
      'about'            => new sfWidgetFormTextarea(),
      'specialities'     => new sfWidgetFormTextarea(array(), array('class' => 'text-select', 'rel' => 'specialities')),
      'regions'          => new sfWidgetFormTextarea(array(), array('class' => 'text-select', 'rel' => 'regions')),
      'experience'       => new sfWidgetFormInputText(array(), array('maxlength' => 20)),
      'advantage'        => new sfWidgetFormTextarea(),
      'site'             => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'country'          => new sfValidatorRegex(array('pattern' => '/^\+\d+$/', 'required' => false)),
      'area'             => new sfValidatorNumber(array('required' => false)),
      'number'           => new sfValidatorCleanNumber(array('required' => false)),
      'about'            => new sfValidatorString(array('required' => false)),
      'specialities'     => new sfValidatorString(array('required' => false)),
      'regions'          => new sfValidatorString(array('required' => false)),
      'experience'       => new sfValidatorString(array('required' => false)),
      'advantage'        => new sfValidatorString(array('required' => false)),
      'site'             => new sfValidatorUrl(
        array('required' => false),
        array('invalid' => 'Неправильно заполнено поле "Адрес сайта"')
      ),
    ));

    $names = $this->object->field_names;
    foreach ($this as $field_name => $field) {
      if (!isset($names[$field_name])) {
        if (!in_array($field_name, array('country', 'area', 'number'))) {
          unset($this[$field_name]);
        }
      }
    }

    $this->widgetSchema->setLabels(array_merge($names, array('country' => $names['additional_phone'])));

    $this->widgetSchema->setNameFormat('user_info[%s]');
    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
  }

  public function getValues()
  {
    $values = parent::getValues();

    if (!empty($values['area']) && !empty($values['number'])) {
      $phone = Toolkit::formatPhoneNumber($values['country'], $values['area'], $values['number']);
    }
    else {
      $phone = null;
    }
    unset($values['country'], $values['area'], $values['number']);

    foreach ($values as $key => $value) {
      if (empty($value)) {
        $values[$key] = null;
      }
    }

    return array_merge($values, array('additional_phone' => $phone));
  }

  public function updateObject($values = null)
  {
    $values = $this->processValues($this->getValues());
    $this->object->fromArray($values);
    return $this->object;
  }

  protected function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();
    if ($this->object->additional_phone) {
      $phone = Toolkit::unformatPhoneNumber($this->object->additional_phone);
      if ($phone) {
        $phone['country'] = '+' . $phone['country'];
        $this->setDefaults(array_merge($this->getDefaults(), $phone));
      }
    }
  }
}