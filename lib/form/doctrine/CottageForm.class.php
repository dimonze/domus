<?php

/**
 * Cottage form.
 *
 * @package    domus
 * @subpackage form
 * @author     Garin Studio
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class CottageForm extends BaseCottageForm
{
  public function configure()
  {
    parent::configure();
    
    $this->widgetSchema['currency'] = new sfWidgetFormChoice(array('choices' =>
      array(
        'rur' => 'РУБ',
        'usd' => 'USD',
        'eur' => 'EUR',
        )));
    
    $this->widgetSchema['type'] = new sfWidgetFormInputHidden();
    $this->setDefault('type', 'cottage');
    
    unset($this['lot_id']);
  }
  
  public function updateObject($values = null) {
    $this->values = $this->validatorSchema->clean($values);
    parent::updateObject($this->values);
  }
}
