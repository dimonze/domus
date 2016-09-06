<?php

/**
 * sfValidatorChoiceKeys validates values exists on keys.
 *
 * @package    domus
 * @subpackage validator
 * @author     Garin Studio
 */
class sfValidatorChoiceKeys extends sfValidatorChoice {

  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * choices:  An array of expected values (required)
   *  * multiple: true if the select tag must allow multiple selections
   *
   * @param array $options    An array of options
   * @param array $messages   An array of error messages
   *
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->addRequiredOption('choices');
    $this->addOption('multiple', false);
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    $choices = $this->getOption('choices');
    if ($choices instanceof sfCallable)
    {
      $choices = $choices->call();
    }

    if ($this->getOption('multiple'))
    {
      if (!is_array($value))
      {
        $value = array($value);
      }

      foreach ($value as $v)
      {
        if (!self::inChoices($v, $choices))
        {
          throw new sfValidatorError($this, 'invalid', array('value' => $v));
        }
      }
    }
    else
    {
      if (!self::inChoices($value, $choices))
      {
        throw new sfValidatorError($this, 'invalid', array('value' => $value));
      }
    }

    return $value;
  }
  
  /**
   * Checks if a value is part of given keys of choices
   *
   * @param  mixed $value   The value to check
   * @param  array $choices The array of available choices
   *
   * @return Boolean
   */
  static protected function inChoices($value, array $choices = array())
  {    
    if (in_array((string) $value, array_keys($choices))){
      return true;
    }

    return false;
  }

}