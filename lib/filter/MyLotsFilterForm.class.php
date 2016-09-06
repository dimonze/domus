<?php

/**
 * MyLotsFilterForm
 *
 * @package    domus
 * @subpackage filters
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class MyLotsFilterForm extends sfForm
{

  protected $_types = array('' => 'Выберите тип недвижимости');

  public function __construct($defaults = array(), $options = array(), $CSRFSecret = null){
    foreach (sfConfig::get('app_lot_types') as $type => $names) {
      $this->_types[$type] = $names['name'];
    }
    parent::__construct($defaults, $options, $CSRFSecret);
  }

  public function configure() {
    $this->setWidgets(array(
      'type'    => new sfWidgetFormSelect(array(
          'choices' => $this->_types
        ),array(
          'class' => 'select_06'
        )),
      'region_id' => new sfWidgetFormDoctrineChoice(array(
          'model' => 'Region',
          'add_empty' => 'Выберите регион'
          ),array(
          'class' => 'select_06'
        ))
    ));

    $this->setValidators(array(
      'type'       => new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->_types))),
      'region_id'  => new sfValidatorDoctrineChoice(array('model' => 'Region', 'required' => false))
    ));

    $this->widgetSchema->setNameFormat('filter[%s]');
  }
  
}