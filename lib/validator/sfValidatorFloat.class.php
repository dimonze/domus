<?php

/**
 * sfValidatorNumber validates a number (integer or float). It also converts the input value to a float.
 *
 * @package    domus
 * @subpackage validator
 * @author     Garin Studio
 * @version    SVN: $Id: sfValidatorNumber.class.php 11476 2008-09-12 12:48:38Z fabien $
 */
class sfValidatorFloat extends sfValidatorNumber {

  protected function doClean($value)
  {
    $value = str_replace(',', '.', $value);
    return parent::doClean($value);
  }

}
