<?php

/**
 * sfWidgetFormInputMultiple
 *
 * @package    domus
 * @subpackage widget
 * @author     Garin Studio
 * @version    SVN: $Id: sfWidgetFormInput.class.php 9046 2008-05-19 08:13:51Z FabianLange $
 */
class sfWidgetFormInputMultiple extends sfWidgetForm
{
  /**
   * Constructor.
   *
   * Available options:
   *
   *  * inputs: input list
   *  * format: format, default: %label% %input%
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetFormInput
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addRequiredOption('inputs');
    $this->addOption('format', '%label% %input%');
  }

  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $html = '';
    foreach ($this->getOption('inputs') as $i_name => $i_input) {
      $i_value = isset($value[$i_name]) ? $value[$i_name] : null;
      $i_name =  sprintf('%s[%s]', $name, $i_name);
      
      $html .=  $this->renderContentTag('label', str_replace(
          array('%label%', '%input%'),
          array($i_input->getLabel(), $i_input->render($i_name, $i_value)),
          $this->getOption('format')
        )).' ';
    }
    return $html;
  }
}
