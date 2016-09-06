<?php

/**
 * sfWidgetFormInputPrice represents an HTML input tag with price selector.
 *
 * @package    domus
 * @subpackage widget
 * @author     Garin Studio
 * @version    SVN: $Id: sfWidgetFormInput.class.php 9046 2008-05-19 08:13:51Z FabianLange $
 */
class sfWidgetFormInputPrice extends sfWidgetFormInput
{
  public function __construct($options = array(), $attributes = array())
  {
    if (isset($attributes['class'])) {
      $attributes['class'] += ' inputprice';
    }
    else {
      $attributes['class'] = 'inputprice';
    }

    parent::__construct($options, $attributes);

    $this->currency_widget = new sfWidgetFormSelect(array('choices' => $this->getOption('currency')), array('class' => 'inputprice'));
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
    $this->addRequiredOption('currency');
    $this->addOption('format', '%value% %currency%');
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
      $currency = substr($value, 0, 3);
      $value = substr($value, 3);
    }
    else {
      $currency = null;
    }

    return str_replace(
      array(
        '%value%',
        '%currency%'
      ),
      array(
        $this->renderTag('input', array_merge(array('type' => $this->getOption('type'), 'name' => $name, 'value' => $value), $attributes)),
        $this->currency_widget->render('currency_'.$name, $currency, $attributes)
      ),
      $this->getOption('format')
    );
  }
}
