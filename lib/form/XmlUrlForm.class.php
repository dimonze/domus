<?php

/**
 * BlogUser form.
 *
 * @package    form
 * @subpackage BlogUser
 */
class XmlUrlForm extends BaseForm
{
  public function configure()
  {
    $this->widgetSchema->setNameFormat('xml_url[%s]');

    $this->setWidget('xml_url', new sfWidgetFormInputText());
    $this->setValidator('xml_url', new sfValidatorUrl(
        array('required' => true)
      )
    );
    $this->widgetSchema->setLabels(array(
      'xml_url' =>  'Адрес xml файла'
    ));

    AjaxForm::setErrorMessages($this);
  }

  public function getErrorsArray()
  {
    return AjaxForm::getErrorsArray($this);
  }
}