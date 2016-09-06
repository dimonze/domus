<?php

/**
 * ImportOrder form.
 *
 * @package    form
 * @subpackage ImportOrder
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class ImportOrderDataForm extends BaseForm implements AjaxFormInterface
{
  public function configure()
  {
    $this->widgetSchema['fullname']         = new sfWidgetFormInputText(array(), array('maxlength' => 148));
    $this->widgetSchema['phone']            = new sfWidgetFormInputText();
    $this->widgetSchema['email']            = new sfWidgetFormInputText();
    $this->widgetSchema['start']            = new sfWidgetFormInputText(array(), array('class' => 'datepicker'));
    $this->widgetSchema['jur_addr']         = new sfWidgetFormTextarea(array(), array('rows' => 5, 'cols' => '30'));
    $this->widgetSchema['post_addr']        = new sfWidgetFormTextarea(array(), array('rows' => 5, 'cols' => '30'));
    $this->widgetSchema['finance_inn']      = new sfWidgetFormInputText(array(), array('maxlength' => 10));
    $this->widgetSchema['finance_kpp']      = new sfWidgetFormInputText(array(), array('maxlength' => 10));
    $this->widgetSchema['finance_ogrn']     = new sfWidgetFormInputText(array(), array('maxlength' => 13));
    $this->widgetSchema['i_agree']          = new sfWidgetFormInputText(array('type' => 'checkbox'));
    $this->widgetSchema['type']             = new sfWidgetFormInputHidden();

    $this->validatorSchema['fullname']      = new sfValidatorString(array('required' => true, 'max_length' => 148));
    $this->validatorSchema['phone']         = new sfValidatorString(array('required' => true, 'max_length' => 25));
    $this->validatorSchema['email']         = new sfValidatorEmail(array('required' => true));
    $this->validatorSchema['start']         = new sfValidatorDate(array('required' => true));
    $this->validatorSchema['jur_addr']      = new sfValidatorString();
    $this->validatorSchema['post_addr']     = new sfValidatorString();
    $this->validatorSchema['finance_inn']   = new sfValidatorInteger();
    $this->validatorSchema['finance_kpp']   = new sfValidatorInteger();
    $this->validatorSchema['finance_ogrn']  = new sfValidatorInteger();
    $this->validatorSchema['i_agree']       = new sfValidatorBoolean(
      array('required' => true),
      array('required' => 'Для продолжения вам необходимо согласиться с договором офертой.')
    );
    $this->validatorSchema['type']          = new sfValidatorPass();
    

    $this->widgetSchema['start']->setDefault(date('d.m.Y', strtotime('+1 day')));

    $this->widgetSchema->setLabels(array(
      'fullname'      =>  'Полное название юрлица',
      'start'         =>  'Дата начала импорта',
      'phone'         =>  'Контактный телефон',
      'email'         =>  'Email',
      'jur_addr'      =>  'Юридический адрес',
      'post_addr'     =>  'Почтовый адрес',
      'finance_inn'   =>  'ИНН',
      'finance_kpp'   =>  'КПП',
      'finance_ogrn'  =>  'ОГРН',
      'type'          =>  'Оплаченные типы недвижимости'
    ));

    $this->widgetSchema->setNameFormat('data[%s]');

    AjaxForm::setErrorMessages($this);
  }

  public function getErrorsArray ()
  {
    return AjaxForm::getErrorsArray($this);
  }
}
