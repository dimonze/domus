<?php

/**
 * sfWidgetFormGMap
 *
 * @package    domus
 * @subpackage widget
 * @author     Garin Studio
 * @version    SVN: $Id: sfWidgetFormInput.class.php 9046 2008-05-19 08:13:51Z FabianLange $
 */
class sfWidgetFormGMap extends sfWidgetForm
{
  /**
   * Constructor.
   *
   * Available options:
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetFormInput
   */
  protected function configure($options = array(), $attributes = array())
  {
    
  }

  public function getJavaScripts() {
    return array(
      'http://maps.googleapis.com/maps/api/js?sensor=false&key='.sfConfig::get('app_gmap_key')
      , 'jquery', 'form');
  }

  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    return 
      $this->renderTag('input', array_merge(array('type' => 'hidden', 'name' => $name, 'value' => $value), $attributes)) .
      $this->renderContentTag('div', '&nbsp;', array('class' => 'input-gmap'));
  }
}
