<?php

/**
 * GeoIpRu form base class.
 *
 * @method GeoIpRu getObject() Returns the current form's model object
 *
 * @package    domus
 * @subpackage form
 * @author     Garin Studio
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseGeoIpRuForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'start'     => new sfWidgetFormInputHidden(),
      'end'       => new sfWidgetFormInputHidden(),
      'region_id' => new sfWidgetFormInputText(),
      'region'    => new sfWidgetFormInputText(),
      'city'      => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'start'     => new sfValidatorChoice(array('choices' => array($this->getObject()->get('start')), 'empty_value' => $this->getObject()->get('start'), 'required' => false)),
      'end'       => new sfValidatorChoice(array('choices' => array($this->getObject()->get('end')), 'empty_value' => $this->getObject()->get('end'), 'required' => false)),
      'region_id' => new sfValidatorInteger(array('required' => false)),
      'region'    => new sfValidatorString(array('max_length' => 80, 'required' => false)),
      'city'      => new sfValidatorString(array('max_length' => 60, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('geo_ip_ru[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'GeoIpRu';
  }

}
