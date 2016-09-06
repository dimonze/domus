<?php

class NewbuildingSearchFilterForm extends sfForm
{
	public function configure() {
	    $this->setWidgets(array(
        	'region_id' => new sfWidgetFormChoice(array('choices' =>
        		array(
        			77 => 'Москва',
        			50 => 'Подмосковье'
        		),         		
        		'label' => '* Регион')),
	        'location' => new sfWidgetFormRegionnode(array(
		        'source'  => 'form/regionnode',
		        'choices' => array('' => 'Выберите регион'),
		        'label'   => '* Метро/Район/Город'
		      )),
	        'price' => new sfWidgetFormInputRange(),
	        'area' => new sfWidgetFormInputRange(),
	        'currency' => new sfWidgetFormSelect(array(
	          'choices' => DynamicForm::$currencies
	        )),
	    ));
	    
	    $this->setValidators(array(
	        'region_id' => new sfValidatorDoctrineChoice(array('model' => 'Region')),
	        'location' => new sfValidatorDoctrineChoice(array('model' => 'Regionnode', 'required' => false)),
	        'price' => new sfValidatorIntegerRange(array(
	            'required' => false
	        )),
	        'currency' => new sfValidatorChoice(array(
	          'choices' => array_keys(DynamicForm::$currencies),
	          'required' => false
	        )),
	    ));
	    
	    $this->getWidgetSchema()->setLabels(array(
	        'region_id' => 'Выбор региона',
	        'location' => 'Выбор метро/населенного пункта',
	        'price' => 'Цена',
	        'area' => 'Площадь, м<sup>2</sup>',
	    ));
	    
  }
}