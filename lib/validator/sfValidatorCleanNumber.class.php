<?php

/**
 * sfValidatorCleanNumber removes non-digit characters from string
 *
 * @package    domus
 * @subpackage validator
 * @author     Garin Studio
 * @version    SVN: $Id: sfValidatorNumber.class.php 11476 2008-09-12 12:48:38Z fabien $
 */
class sfValidatorCleanNumber extends sfValidatorNumber {

  protected function doClean($value)
  {
    $clean = preg_replace('/\D+/', '', $value);
    if ($clean === '') {
      throw new sfValidatorError($this, 'invalid', array('value' => $value));
    }
    return $clean;
  }

}
