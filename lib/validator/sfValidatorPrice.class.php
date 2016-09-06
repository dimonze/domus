<?php

/**
 * @package    domus
 * @subpackage validator
 * @author     Garin Studio
 * @version    SVN: $Id: sfValidatorNumber.class.php 11476 2008-09-12 12:48:38Z fabien $
 */
class sfValidatorPrice extends sfValidatorNumber {

  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);
    $this->addRequiredOption('currencies');
  }

  protected function doClean($value)
  {
    $currency = substr($value, 0, 3);
    $value = (int) preg_replace('/\D/', '', substr($value, 3));

    if (!in_array($currency, $this->getOption('currencies'))) {
      throw new sfValidatorError($this, 'invalid', array('value' => $currency));
    }

    $value = parent::doClean($value);

    return $currency.$value;
  }

}
