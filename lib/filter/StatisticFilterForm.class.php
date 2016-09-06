<?php

/**
 * StatisticFilterForm
 *
 * @package    domus
 * @subpackage filters
 * @author     Garin Studio
 */
class StatisticFilterForm extends sfForm
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
      'type' => new sfWidgetFormSelect(array(
          'choices' => $this->_types
        )
      ),
      'region' => new sfWidgetFormDoctrineChoice(array(
          'model'     => 'Region',
          'add_empty' => 'Выберите регион'
        )
      ),
      'date_from' => new sfWidgetFormInput(array(), array('class' => 'datepicker')),
      'date_to'   => new sfWidgetFormInput(array(), array('class' => 'datepicker')),
      'usertype1' => new sfWidgetFormInputCheckbox(array('label' => 'Компания')),
      'usertype2' => new sfWidgetFormInputCheckbox(array('label' => 'Сотрудник')),
      'usertype3' => new sfWidgetFormInputCheckbox(array('label' => 'Частный<br/>риэлтор')),
      'usertype4' => new sfWidgetFormInputCheckbox(array('label' => 'Собственник')),
      'usertype5' => new sfWidgetFormInputCheckbox(array('label' => 'Источник')),
    ));
    
    $this->setValidators(array(
      'type'      => new sfValidatorPass(),
      'region'    => new sfValidatorPass(),
      'date_from' => new sfValidatorPass(),
      'date_to'   => new sfValidatorPass(),
      'usertype1' => new sfValidatorPass(),
      'usertype2' => new sfValidatorPass(),
      'usertype3' => new sfValidatorPass(),
      'usertype4' => new sfValidatorPass(),
      'usertype5' => new sfValidatorPass(),
    ));
    
    $this->setDefaults(array(
      'usertype1' => true,
      'usertype2' => true,
      'usertype3' => true,
      'usertype4' => true,
      'date_from' => date('d.m.Y', strtotime('-1 week')),
      'date_to'   => date('d.m.Y')
    ));
    
    $this->widgetSchema->setLabels(array(
      'type'    =>  'Тип:',
      'region'  =>  'Регион:'
    ));
    $this->widgetSchema->setNameFormat('filter[%s]');
  }
}