<?php

/**
 * ModerateFilterForm
 *
 * @package    domus
 * @subpackage filters
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class ModerateFilterForm extends sfForm
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
      'id' => new sfWidgetFormInput(array(), array('class' => 'input_01')),
      'username' => new sfWidgetFormInput(array(), array('class' => 'input_01')),
      'email' => new sfWidgetFormInput(array(), array('class' => 'input_01')),
      'created_at_from' => new sfWidgetFormInput(array(), array('class' => 'input_02b datepicker')),
      'created_at_to' => new sfWidgetFormInput(array(), array('class' => 'input_02b datepicker')),
      'active_till_from' => new sfWidgetFormInput(array(), array('class' => 'input_02b datepicker')),
      'active_till_to' => new sfWidgetFormInput(array(), array('class' => 'input_02b datepicker')),
      'address' => new sfWidgetFormInput(array(), array('class' => 'input_01')),
      'usertype1' => new sfWidgetFormInputCheckbox(array('label' => 'Компания')),
      'usertype2' => new sfWidgetFormInputCheckbox(array('label' => 'Сотрудник')),
      'usertype3' => new sfWidgetFormInputCheckbox(array('label' => 'Частный<br/>риэлтор')),
      'usertype4' => new sfWidgetFormInputCheckbox(array('label' => 'Собственник')),
      'usertype5' => new sfWidgetFormInputCheckbox(array('label' => 'Источник')),
      'status' => new sfWidgetFormSelect(array(
          'choices' => array(
              '' => 'Статус',
              'active' => 'активно',
              'inactive' => 'неактивно',
              'restricted' => 'запрещено',
              'moderate' => 'перемодерация',
              'deleted' => 'удалено'
            )
        ),array(
          'class' => 'select_02'
        )),
      'per-page' => new sfWidgetFormSelect(array(
          'choices' => array(
              '' => 'На странице',
              10 => 10, 20 => 20, 50 => 50, 100 => 100, 200 => 200,
              500 => 500, 1000 => 1000, 5000 => 5000
            )
        ),array(
          'class' => 'select_02'
        )),
      'region_id' => new sfWidgetFormDoctrineChoice(array(
          'model' => 'Region',
          'add_empty' => 'Выберите регион'
          ),array(
          'class' => 'select_01'
        )),
      'type' => new sfWidgetFormSelect(array(
          'choices' => $this->_types
        ),array(
          'class' => 'select_01'
        )),
      'description' => new sfWidgetFormInput(array(), array('class' => 'input_01')),
      'phone' => new sfWidgetFormInput(array(), array('class' => 'input_01')),
      'num_rooms' => new sfWidgetFormInput(array(), array('class' => 'input_01')),
      'area_from' => new sfWidgetFormInput(array(), array('class' => 'input_01')),
      'area_to' => new sfWidgetFormInput(array(), array('class' => 'input_01')),
      'area_country_from' => new sfWidgetFormInput(array(), array('class' => 'input_01')),
      'area_country_to' => new sfWidgetFormInput(array(), array('class' => 'input_01')),
      'price_from' => new sfWidgetFormInput(array(), array('class' => 'input_01')),
      'price_to' => new sfWidgetFormInput(array(), array('class' => 'input_01')),
      'sort' => new sfWidgetFormSelect(array(
          'choices' => array(
            'id-desc' => 'id &darr;',
            'id-asc'  => 'id &uarr;',
            'type-desc'=> 'типу &darr;',
            'type-asc' => 'типу &uarr;',
            'address-asc' => 'адресу &uarr;',
            'address-desc' => 'адресу &darr;',
            'price-asc' => 'цене &uarr;',
            'price-desc' => 'цене &darr;',
            'email-asc' => 'логину &uarr;',
            'email-desc' => 'логину &darr;',
            'created_at-asc' => 'размещено &uarr;',
            'created_at-desc' => 'размещено &darr;',
            'active_till-asc' => 'закончится &uarr;',
            'active_till-desc' => 'закончится &darr;',
          )
        ),array(
          'class' => 'select_01'
      )),
      'coords' => new sfWidgetFormInput(array(), array('type' => 'hidden')),
      'region_center_coords' => new sfWidgetFormInput(array(), array('type' => 'hidden')),
      'map_search' => new sfWidgetFormInputCheckbox(array('label' => 'Поиск по карте')),
      'page' => new sfWidgetFormInputHidden()
    ));

    
    $this->setValidators(array(
      'id' => new sfValidatorPass(),
      'username' => new sfValidatorPass(),
      'email' => new sfValidatorPass(),
      'created_at_from' => new sfValidatorPass(),
      'created_at_to' => new sfValidatorPass(),
      'active_till_from' => new sfValidatorPass(),
      'active_till_to' => new sfValidatorPass(),
      'address' => new sfValidatorPass(),
      'usertype1' => new sfValidatorPass(),
      'usertype2' => new sfValidatorPass(),
      'usertype3' => new sfValidatorPass(),
      'usertype4' => new sfValidatorPass(),
      'usertype5' => new sfValidatorPass(),
      'status' => new sfValidatorPass(),
      'per-page' => new sfValidatorPass(),
      'region_id' => new sfValidatorPass(),
      'type' => new sfValidatorPass(),
      'description' => new sfValidatorPass(),
      'phone' => new sfValidatorPass(),
      'num_rooms' => new sfValidatorPass(),
      'area_from' => new sfValidatorPass(),
      'area_to' => new sfValidatorPass(),
      'area_country_from' => new sfValidatorPass(),
      'area_country_to' => new sfValidatorPass(),
      'price_from' => new sfValidatorPass(),
      'price_to' => new sfValidatorPass(),
      'sort' => new sfValidatorPass(),
      'coords' => new sfValidatorPass(),
      'region_center_coords' => new sfValidatorPass(),
      'map_search' => new sfValidatorPass(),
      'page' => new sfValidatorPass(),
    ));

    $this->setDefaults(array(
      'usertype1' => true,
      'usertype2' => true,
      'usertype3' => true,
      'usertype4' => true      
    ));
    $this->widgetSchema->setNameFormat('filter[%s]');
  }
  
}