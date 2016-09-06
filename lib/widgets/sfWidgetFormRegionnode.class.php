<?php

/**
 * sfWidgetFormRegionnode
 *
 * @package    domus
 * @subpackage widget
 * @author     Garin Studio
 * @version    SVN: $Id: sfWidgetFormInput.class.php 9046 2008-05-19 08:13:51Z FabianLange $
 */
class sfWidgetFormRegionnode extends sfWidgetFormSelect
{
  public function __construct($options = array(), $attributes = array())
  {
    if (isset($attributes['class'])) {
      $attributes['class'] += ' regionnode';
    }
    else {
      $attributes['class'] = 'regionnode';
    }
    
    if (isset($options['source'])) {
      $attributes['source'] = sfContext::getInstance()->getController()->genUrl($options['source']);
    }

    parent::__construct($options, $attributes);
  }

  /**
   * Constructor.
   *
   * Available options:
   *
   *  * format: Render format ('%value% %currency%' by default)
   *  * currency: List of currencies
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetFormInput
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addRequiredOption('source');
    parent::configure($options = array(), $attributes = array());
  }

  public function getJavaScripts() {
    return array('jquery', 'form');
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
    if ($value !== null) {
      if (is_array($value)) {
        $value = implode(',', $value);
      }
      $value = $this->renderTag('input', array('type' => 'hidden', 'name' => $name.'_value', 'value' => $value));
    }
    $name .= '[]';
    
    return $value . parent::render($name, null, $attributes, $errors);
  }
}
