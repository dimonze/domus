<?php
/**
 * sfWidgetFormAjaxUpload represents an HTML input file tag with ajax submit.
 *
 * @package    domus
 * @subpackage widget
 * @author     Garin Studio
 * @version    SVN: $Id: sfWidgetFormInput.class.php 9046 2008-05-19 08:13:51Z FabianLange $
 */
class sfWidgetFormAjaxUpload extends sfWidgetForm {
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
    $this->addRequiredOption('url');
    
    $this->addOption('default-type', 'file');
    $this->addOption('max', 0);
    $this->addOption('size', 46);
  }

  public function getJavaScripts() {
    return array('jquery', 'ajaxupload', 'form');
  }

  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $url = sfContext::getInstance()->getController()->genUrl($this->getOption('url'));

    $html  = $this->renderContentTag('div', ' ', array('class' => 'ajax-uploaded'));
    $html .= '<p>
                <span rel="file"><a href="#" class="inner">с компьютера</a></span>
                <span rel="link"><a href="#" class="inner">из интернета</a></span>
              </p>';
    $html .= $this->renderTag('input', array_merge(array('type' => 'file', 'size' => $this->getOption('size')), $attributes));
    $html .= $this->renderTag('input', array_merge(array('type' => 'text', 'size' => $this->getOption('size')), $attributes));
    $html .= $this->renderContentTag('button', 'Загрузить', array(
      'class'         => 'ajax-upload',
      'url'           => $url,
      'max'           => $this->getOption('max'),
      'default-type'  => $this->getOption('default-type'),
      'rel'           => $name
    ));
    
    if ($value != null) {
      if (!is_array($value)) {
        $value = array($value);
      }
      foreach ($value as $v) {
        $html .= $this->renderTag('input', array('type' => 'hidden', 'name' => $name, 'value' => $v));
      }
    }

    return $html;
  }
}