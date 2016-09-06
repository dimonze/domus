<?php

/**
 * GeoIpRu filter form base class.
 *
 * @package    domus
 * @subpackage filter
 * @author     Garin Studio
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 29570 2010-05-21 14:49:47Z Kris.Wallsmith $
 */
abstract class BaseGeoIpRuFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'region_id' => new sfWidgetFormFilterInput(),
      'region'    => new sfWidgetFormFilterInput(),
      'city'      => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'region_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'region'    => new sfValidatorPass(array('required' => false)),
      'city'      => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('geo_ip_ru_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'GeoIpRu';
  }

  public function getFields()
  {
    return array(
      'start'     => 'Number',
      'end'       => 'Number',
      'region_id' => 'Number',
      'region'    => 'Text',
      'city'      => 'Text',
    );
  }
}
