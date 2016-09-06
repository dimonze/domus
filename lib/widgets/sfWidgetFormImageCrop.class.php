<?php
/**
 * sfWidgetFormPhotoCrop represents an HTML input file tag with ajax submit.
 *
 * @package    domus
 * @subpackage widget
 * @author     Garin Studio
 * @version    SVN: $Id: sfWidgetFormInput.class.php 9046 2008-05-19 08:13:51Z FabianLange $
 */
class sfWidgetFormImageCrop extends sfWidgetForm {
  protected function configure($options = array(), $attributes = array()) {
    $this->addRequiredOption('url');
    $this->addOption('type');
  }

  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $url = sfContext::getInstance()->getController()->genUrl($this->getOption('url'));

    $html  = $this->renderContentTag('div', ' ', array('class' => 'title-photo'));    
    $html .= $this->renderTag('input', array('name' => $name, 'type' => 'hidden', 'value' => $value));
    if ($this->hasOption('type')){
      $html .= $this->renderTag('input', array('name' => 'type', 'type' => 'hidden', 'value' => $this->getOption('type')));
    }
    
    if (sfContext::getInstance()->getUser()->type == 'company') {
      $suffix = 'логотип';
    } else {
      $suffix = 'фото';
    }
    $action = 'Загрузить';
    if (!empty (sfContext::getInstance()->getUser()->getObject()->photo)) {
      $action = 'Обновить';
    }
    
    $html .= $this->renderContentTag('button',  $action . ' ' . $suffix, array(
      'class'         => 'photo-crop',
      'url'           => $url,
      'rel'           => $name
    ));

    return $html;
  }
}