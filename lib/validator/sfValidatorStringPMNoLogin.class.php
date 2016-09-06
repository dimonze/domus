<?php

/**
 * @package    domus
 * @subpackage validator
 * @author     Garin Studio
 * @version    SVN: $Id: sfValidatorString.class.php 11476 2008-09-12 12:48:38Z fabien $
 */
class sfValidatorStringPMNoLogin extends sfValidatorString {

  protected function doClean($value)
  {
    $value = (string) strip_tags($value);
    $value = parent::doClean($value);

    return $value;
  }

}
