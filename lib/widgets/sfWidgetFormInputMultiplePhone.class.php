<?php

/**
 * sfWidgetFormInputMultiplePhone
 *
 * @package    domus
 * @subpackage widget
 * @author     Garin Studio
 * @version    SVN: $Id: sfWidgetFormInput.class.php 9046 2008-05-19 08:13:51Z FabianLange $
 */
class sfWidgetFormInputMultiplePhone extends sfWidgetForm
{
  /**
   * Constructor.
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetFormInput
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addOption('default', array('country' => '', 'area' => '', 'number' => ''));
  }

  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    if (!$value) {
      $value = $this->getOption('default');
    }

    if (!isset($value[0])) {
      $value = array($value);
    }

    $default_attributes = array('country' => array('size' => 2), 'area' => array('size' => 4), 'number' => array('size' => 8));

    $html = '';

    foreach ($value as $input_row) {
      $row = '';
      foreach ($input_row as $input_name => $input_value) {
        if (isset($attributes[$input_name])) {
          $input_attributes = $attributes[$input_name];
        }
        elseif (isset($default_attributes[$input_name])) {
          $input_attributes = $default_attributes[$input_name];
        }
        else {
          $input_attributes = array();
        }
        $row .= $this->renderTag('input', array_merge($input_attributes, array('type' => 'text', 'name' => "{$name}[$input_name][]", 'class' => 'input-'.$input_name, 'value' => $input_value)));
      }
      $html .= $this->renderContentTag('div', $row, array('class' => 'phone-row'));
    }

    
    return $html;
  }
}
