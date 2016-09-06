<?php

class sfValidatorIntegerRange extends sfValidatorInteger
{
  protected function doClean($values)
  {
    $clean = array();
    $required = ($this->hasOption('required') && $this->getOption('required'));
    
    if (is_array($values)) {
      foreach ($values as $k => $value ) {
        if(!empty($value) || $required) {
          $clean[$k] = parent::doClean($value);
        } else {
          $clean[$k] = '';
        }
      }
    }
    else {
      $clean = parent::doClean($values);
    }

    return $clean;
  }
}