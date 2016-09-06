<?php

/**
 * BaseUsersSearchFilterForm
 *
 * @package    domus
 * @subpackage filters
 * @author     Garin Studio
 * @version    
 */

class CottagesaleUsersSearchFilterForm extends BaseUsersSearchFilterForm
{
  protected $type = 'cottage-sale';
  
  public function configure() {
    parent::configure();
    
    $this->getWidget('field_94')->setLabel('Площадь участка, сот.');
    $this->getWidgetSchema()->moveField('field_107', sfWidgetFormSchema::BEFORE, 'field_92');
    
    $this->setWidget('square', new sfWidgetFormInputRange(array(), array(
      'class' => 'short_input'
    )));
    $this->setValidator('square', new sfValidatorIntegerRange(array(
        'required' => false
    )));
    $this->getWidget('square')->setLabel('Площадь дома, м<sup>2</sup>');
    $this->getWidgetSchema()->moveField('square', sfWidgetFormSchema::BEFORE, 'field_94');
  }
  
  protected function getAllowedFields() {
    return array( 
        92, 94, 107
    );
  }
}

?>
