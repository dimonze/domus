<?php

/**
 * UserSourcesLink form.
 *
 * @package    form
 * @subpackage UserSourcesLink
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class UserSourcesLinkForm extends BaseUserSourcesLinkForm
{
  public function configure()
  {
    parent::configure();
    $this->widgetSchema->setNameFormat('source_link[%s]');

    $this->widgetSchema['type'] = new sfWidgetFormChoice(array(
      'choices'   =>  UserSourcesLink::$types
    ));

    $this->widgetSchema['file_type'] = new sfWidgetFormChoice(array(
      'choices'   =>  ImportFile::$types
    ));

    $this->widgetSchema['frequency'] = new sfWidgetFormChoice(
      array('choices' => UserSourcesLink::$frequencies)
    );

    $this->widgetSchema['url']  = new sfWidgetFormInputText(
      array(),
      array('value' => 'http://')
    );

    $this->validatorSchema['type'] = new sfValidatorChoice(array(
        'choices'   => array_keys(UserSourcesLink::$types),
        'required'  =>  true
      ),
      array(
        'required'  =>  'Необходимо выбрать формат источника импортируемого файла.',
        'invalid'   =>  'Вы выбрали неправильный формат источника.'
      )
    );

    $this->validatorSchema['file_type'] = new sfValidatorChoice(array(
        'choices'   => array_keys(ImportFile::$types),
        'required'  =>  true
      ),
      array(
        'required'  =>  'Необходимо выбрать формат импортируемого файла.',
        'invalid'   =>  'Вы выбрали неправильный формат файла.'
      )
    );

    $this->validatorSchema['url'] = new sfValidatorUrl(array(
        'required'  =>  true
      ),
      array(
        'invalid' =>  'Неправильный адрес.'
      )
    );

    $this->validatorSchema['frequency'] = new sfValidatorChoice(
      array(
        'required'  =>  true,
        'choices'   =>  array_keys(UserSourcesLink::$frequencies)
      ),
      array(
        'required'  =>  'Вы не указали тип файла для импорта.',
        'invalid'   =>  'Вы указали неправильный тип файла.'
      )
    );

    $this->widgetSchema->setLabels(array(
      'type'      =>  'Формат источника',
      'file_type' =>  'Тип файла',
      'url'       =>  'Ссылка на файл',
      'frequency' =>  'Частота загрузки файла'
    ));

    AjaxForm::setErrorMessages($this);
  }

  public function getErrorsArray()
  {
    return AjaxForm::getErrorsArray($this);
  }
}