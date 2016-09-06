<?php

/**
 * sfWidgetFormRadioCombo
 *
 * @package    domus
 * @subpackage widget
 * @author     Garin Studio
 * @version    SVN: $Id: sfWidgetFormInput.class.php 9046 2008-05-19 08:13:51Z FabianLange $
 */
class sfWidgetFormRadioCombo extends sfWidgetFormSelectRadio
{
  public function __construct($options = array(), $attributes = array())
  {
    if (isset($options['choices'])) {
      $choices = array();
      foreach ($options['choices'] as $choice_val => $choice_label) {
        $choice_val = explode('|', $choice_val);
        $choices[array_pop($choice_val)] = $choice_label;
      }
      $options['choices'] = $choices;
    }
    if (isset($attributes['class'])) {
      $attributes['class'] += ' radiocombo';
    }
    else {
      $attributes['class'] = 'radiocombo';
    }

    parent::__construct($options, $attributes);
  }

  public function getJavaScripts() {
    return array('jquery', 'form');
  }

  /**
   * Constructor.
   *
   * Available options:
   *
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetFormSelectRadio
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);
  }

  /**
   * @param  string $name        The element name
   * @param  string $value       The value displayed in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $html = parent::render($name, $value, $attributes, $errors);
    if ($value !== null) {
      $value = is_array($value) ? $value[0] : $value;
      $html =
        $this->renderTag('input', array('type' => 'hidden', 'id' => $name.'_value', 'value' => $value)).$html;
    }
    return $html;
  }
}
