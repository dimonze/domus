<?php

class sfWidgetFormInputKCaptcha extends sfWidgetFormInput
{

  /**
   * @param array $options
   *  * src:        Src attribute for imgTag
   *  * template:   The HTML template to use to render this widget
   *                  The available placeholders are:
   *                    * input (the field to input captcha)
   *                    * img (captha image)
   * @param array $attributes
   *
   * @return void
   */
  protected function configure($options = array(), $attributes = array()) {
    parent::configure($options, $attributes);

    $this->setAttribute('autocomplete', 'off');
    $this->setOption('type', 'text');
    $this->addOption('src', sfContext::getInstance()->getController()->genUrl('@kcaptcha'));
    $this->addOption('template', '%input%%img%');
  }

  /**
   * Generate HTML code for KCaptcha form widget
   *
   * @param string $name
   *
   * @param string $value
   *
   * @param array $attributes
   *
   * @param array $errors
   *
   * @return string
   */
  public function render($name, $value = null, $attributes = array(), $errors = array()) {
    $attributes = array_merge($this->getAttributes(), $attributes);

    $kcaptchaAttributes = array(
      'src' => $this->getOption('src'),
      'alt' => $this->getLabel(),
      'id' => $this->generateId($name) . '_kcaptcha',
      'onclick' => "this.src='" . $this->getOption('src') . "?'+(new Date).getTime();",
    );


    return strtr(
      $this->getOption('template'),
      array(
        '%input%' => parent::render($name, null, $attributes, $errors),
        '%img%' => $this->renderTag('img', $kcaptchaAttributes)
      )
    );
  }

}
