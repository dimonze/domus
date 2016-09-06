<?php

class sfValidatorKCaptcha extends sfValidatorBase
{
  /**
   * @param string $value
   *
   * @return string
   *
   * @throws sfValidatorError
   */
  protected function doClean($value)
  {
    $clean = (string) $value;

    $kcaptchaKey = sfContext::getInstance()->getUser()->getAttributeHolder()->remove('kcaptcha_key');
    if ($clean !== $kcaptchaKey)
    {
      throw new sfValidatorError($this, 'invalid');
    }

    return $clean;
  }
}