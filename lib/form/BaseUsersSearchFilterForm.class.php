<?php

/**
 * BaseUsersSearchFilterForm
 *
 * @package    domus
 * @subpackage filters
 * @author     Garin Studio
 * @version    
 */

class BaseUsersSearchFilterForm extends sfForm
{
  protected $type = 'new_building-sale';
  public $fields_types = array();
  
  public function __construct($defaults = array(), $options = array(), $CSRFSecret = null){
    parent::__construct($defaults, $options, $CSRFSecret);
  }
  
  public function configure() {
    //Общие поля
    $this->setWidgets(array(
          'regionnode' => new sfWidgetFormRegionnode(array(
          'source'  => '/form/regionnode',
          'choices' => array('' => 'Выберите район'),
          'label'   => 'Метро/Район/Город'
        ), array(
            'id' => 'landing_region_node',
            'class' => 'test'
        ))
        ,'location-type' => new sfWidgetFormSelect(array(
          'choices' => array(
              'form' => 'нет'
              ,'map' => 'да'
          )
        ))
        ,'price' => new sfWidgetFormInputRange(array(), array(
            'class' => 'short_input'
        ))
        ,'currency' => new sfWidgetFormSelect(array(
          'choices' => DynamicForm::$currencies
        ))
        ,'q_text' => new sfWidgetFormInput(array(), array(
            'id' => 'dynamicform_street'
            ,'class' => 'autocomplete-street'
            ,'source' => '/form/street'
        ))
        ,'q_text_enabled' => new sfWidgetFormInputCheckbox(array(
            'value_attribute_value' => 1
        ))
        ,'sort' => new sfWidgetFormInputHidden(array(), array('value' => 'rating-desc'))
    ));
    
    $this->setValidators(array(
        'regionnode' => new sfValidatorPass()
        ,'location-type' => new sfValidatorChoice(array(
          'choices' => array('form', 'map')
        ))
        ,'price' => new sfValidatorIntegerRange(array(
            'required' => false
        ))
        ,'currency' => new sfValidatorChoice(array(
          'choices' => array_keys(DynamicForm::$currencies),
          'required' => false
        ))
        ,'q_text' => new sfValidatorString(array(
            'required' => false
        ))
        ,'q_text_enabled' => new sfValidatorPass()
        ,'sort' => new sfValidatorString()
    ));
    
    $this->getWidgetSchema()->setLabels(array(
        'regionnode' => 'Метро/Район/Шоссе/Город'
        ,'location-type' => 'Включить поиск по карте'
        ,'price' => 'Цена'
        ,'currency' => 'Валюта'
        ,'q_text' => 'Текстовый запрос'
        ,'q_text_enabled' => 'Учитывать запрос'
    ));
    
    //Специфические
    $form_items = Doctrine::getTable('FormItem')
      ->createQuery('f')
      ->where("type = ?", array($this->type));
    
    $allowedFields = $this->getAllowedFieldsIds();
    if(!empty($allowedFields)) {
      $form_items->andWhere('field_id IN ?', array($allowedFields));
    }
        
    $form_items = $form_items->execute();
    $this->renderFieldsFromArray($form_items);
  }
  
  protected function renderFieldsFromArray($form_items) {
    foreach ($form_items as $form_item) {
      $field_type = 'single';
      $field_data = $form_item->getFormField()->toArray();
      //Особая фильтрация значений
      $allowed = $this->getAllowedFields();
      $split_values = true;
      if(!empty($allowed) && isset($allowed[$field_data['id']]) && empty($allowed[$field_data['id']]['exclude']) && is_array($allowed[$field_data['id']])) {
        $field_data['value'] = $allowed[$field_data['id']];
        $split_values = false;
      }

      $name = "field_{$field_data['id']}";
      $label = $field_data['label'];
      $label = empty($field_data['help']) ? $label : $label . ', ' . $field_data['help'];
      
      $required = false;
      $this->setWidget($name, new sfWidgetFormInput());
      
      //Инициализация по типу
      switch ($field_data['type']) {
        case 'float':
          $this->setValidator($name, new sfValidatorFloat());
          break;
        
        case 'integer':
          $this->setValidator($name, new sfValidatorInteger());
          break;
        
        case 'select':
          $multiple = false;
          $choices = $field_data['value'];
          if($split_values) { $choices = preg_split('#\n|\r\n#', $choices); }
          $choices = array_combine($choices, $choices);
          array_unshift($choices, '');
          
          $this->setWidget($name, new sfWidgetFormSelect( array(
              'choices' => $choices,
              'multiple' => $multiple
          ) ));
          $this->setValidator($name, new sfValidatorChoice( array(
              'choices' => array_keys($choices),
              'multiple' => $multiple
          ) ));
          break;
          
        case 'multiple':
        case 'radiocombo':
          $choices = $field_data['value'];
          if($field_data['type'] == 'multiple'){
            if($split_values) { $choices = preg_split('#\n#', $choices, -1, PREG_SPLIT_NO_EMPTY); }
            $choices = array_combine($choices, $choices);
          } else {
            if($split_values) {
              $choices = preg_match('#select:([^\|]+)#su', $field_data['value'], $matches);
              $choices = explode(',', $matches[1]);
            }
          }
          
          $this->setWidget($name, new sfWidgetFormSelectCheckbox( array(
              'choices' => $choices
          ) ));
          $this->setValidator($name, new sfValidatorChoice( array(
              'choices' => $choices,
              'multiple' => true
          ) ));
          $field_type = 'multi';
          break;
          
        case 'radio':
          $choices = $field_data['value'];
          if($split_values) { $choices = preg_split('#\n|\r\n#', $choices, -1, PREG_SPLIT_NO_EMPTY); }
          $choices = array_combine($choices, $choices);
          $this->setWidget($name, new sfWidgetFormSelectRadio( array(
              'choices' => $choices
          ) ));
          $this->setValidator($name, new sfValidatorChoice( array(
              'choices' => $choices
          ) ));
          break;

        default:
          $this->setValidator($name, new sfValidatorString());
          break;
      }
      
      switch ($field_data['id']) {
        case 1:
        case 5:
        case 26:
        case 27:
        case 46:
        case 47:
        case 72:
        case 75:
        case 92:
        case 94:
          $this->setWidget($name, new sfWidgetFormInputRange(array(), array(
            'class' => 'short_input'
          )));
          $this->setValidator($name, new sfValidatorIntegerRange());
          $field_type = 'double';
          break;
        
        case 6:
        case 28:
        case 64:
          $choices = $this->getWidget($name)->getOption('choices');
          $choices[0] = 'Любой';
          $this->getWidget($name)->setOption('choices', $choices);
          $this->getValidator($name)->setOption('choices', array_keys($choices));
          break;
        
        case 54:
        case 55:
          $choices = $this->getWidget($name)->getOption('choices');
          $extra = array( 'комната', 'квартира со свободной планировкой' );
          foreach ($extra as $val) {
            $ind = array_search($val, $choices);
            if($ind != false && $ind != $val) {
              $choices[$val] = $val;
              unset( $choices[$ind] );
            }
          }
          
          $choices[4] = '5+';
          
          $this->getWidget($name)->setOption('choices', $choices);
          $this->getValidator($name)->setOption('choices', array_keys($choices));
          $field_type = 'flats';
          break;
          
        case 76:
          $field_type = 'flats';
          break;
        
        case 107:
          $choices = $field_data['value'];
          $choices = preg_split('#\n|\r\n#', $choices);
          array_unshift($choices, '');
          $choices = array_combine($choices, $choices);

          $this->setWidget($name, new sfWidgetFormSelect( array(
              'choices' => $choices,
              'multiple' => false
          ) ));
          $this->setValidator($name, new sfValidatorChoice( array(
              'choices' => array_keys($choices),
              'multiple' => false
          ) ));
          break;
      }
      
      //Продолжение особой фильтрации
      if($this->getWidget($name)->hasOption('choices') && !empty( $allowed[$field_data['id']]['exclude'] )) {
        $exclude = $allowed[$field_data['id']];
        unset( $exclude['exclude'] );
        
        $choices = $this->getWidget($name)->getOption('choices');
        $choices  = array_diff($choices, $exclude);
        $this->getWidget($name)->setOption('choices', $choices);
        $this->getValidator($name)->setOption('choices', array_keys($choices));
      }
      
      $this->getValidator($name)->setOption('required', $required);
      $this->getWidgetSchema()->setLabel($name, $label);
      
      $this->fields_types[$field_data['id']] = $field_type;
    }
  }
  
  protected function getAllowedFields() {
    return null;
  }
  
  protected function getAllowedFieldsIds() {
    $result = array();
    
    $fields = $this->getAllowedFields();
    if(!empty($fields)) {
      foreach ($fields as $k => $field) {
        if(is_array($field)) {
          $field = $k;
        }
        
        $result[] = $field;
      }
    }
    
    return $result;
  }
}

?>
