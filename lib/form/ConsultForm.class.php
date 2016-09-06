<?php

class ConsultForm extends BaseForm implements AjaxFormInterface
{
  public static $_values = array(
    'type' => array(
      'apartment' => 'Квартира',
      'room'      => 'Комната',
    ),
    'where' => array(
      'msk'              => 'Москва',
      'less15KmFromMKAD' => 'МО до 15 км от МКАД',
    ),
    'price' => array(
      'price5_6'   => '5 млн. - 6 млн.',
      'price6_7'   => '6 млн. - 7 млн.',
      'price7_8'   => '7 млн. - 8 млн.',
      'price8_9'   => '8 млн. - 9 млн.',
      'price9_10'  => '9 млн. - 10 млн.',
      'price10_99' => 'больше 10 млн.',
    ),
    'bool' => array(
      'yes' => 'Да',
      'no'  => 'Нет',
    ),
  );

  public function configure() {
    $this->setWidgets(array(
      'type'          => new sfWidgetFormSelect(array('choices' => self::$_values['type'])),
      'where'         => new sfWidgetFormSelect(array('choices' => self::$_values['where'])),
      'price'         => new sfWidgetFormSelect(array('choices' => self::$_values['price'])),
      'hasrealtor'    => new sfWidgetFormSelectRadio(array('choices' => self::$_values['bool'])),
      'fearsrealtor'  => new sfWidgetFormSelectRadio(array('choices' => self::$_values['bool'])),
      'name'          => new sfWidgetFormInputText(),
      'phone'         => new sfWidgetFormInputText(),
      'email'         => new sfWidgetFormInputText(),
      'comment'       => new sfWidgetFormTextarea(
        array(), 
        array('style' => 'height: 120px; margin: 0px; width: 374px;', 'maxlength' => 300))
    ));
    
    $this->setDefault('hasrealtor', 'no');
    $this->setDefault('fearsrealtor', 'yes');

    $this->setValidators(array(
      'type'         => new sfValidatorString(array('required' => true)),
      'where'        => new sfValidatorString(array('required' => true)),
      'price'        => new sfValidatorString(array('required' => true)),
      'hasrealtor'   => new sfValidatorString(array('required' => true)),
      'fearsrealtor' => new sfValidatorString(array('required' => true)),
      'phone'        => new sfValidatorRegex(array('pattern' => '#^[\+\-\d\s\(\)]+$#', 'required' => true)),
      'name'         => new sfValidatorString(array('required' => true)),
      'email'        => new sfValidatorEmail(array('required' => true)),
      'comment'      => new sfValidatorString(array(
          'max_length' => 300,
          'required' => false
        ),
        array(
          'max_length'  =>  'Комментарий не может быть длиннее 300 символов',
        ))
    ));
    $this->getValidatorSchema()->setOption('allow_extra_fields', true);
    AjaxForm::setErrorMessages($this);
  }
  
  public function getErrorsArray()
  {
    return AjaxForm::getErrorsArray($this);
  }
}