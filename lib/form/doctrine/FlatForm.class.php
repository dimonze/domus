<?php

/**
 * Flat form.
 *
 * @package    form
 * @subpackage Flat
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z fabien $
 */
class FlatForm extends BaseFlatForm
{
  public function configure()
  {
    $this->widgetSchema['rooms'] = new sfWidgetFormSelect(array('choices' => array(
      1 => '1',
      2 => '2',
      3 => '3',
      4 => '4',
      5 => '5',
      6 => '6',
      7 => '7',
      8 => '8',
      9 => '9',
    )));
    $this->widgetSchema['currency'] = new sfWidgetFormChoice(array('choices' =>
      array(
        'rur' => 'РУБ',
        'usd' => 'USD',
        'eur' => 'EUR',
        )));
    $this->widgetSchema->setLabels(array(
      'rooms' => 'Количество комнат',
    ));
  }

  public function updateObject($values = null) {
    $this->values = $this->validatorSchema->clean($values);
    parent::updateObject($this->values);
  }

}