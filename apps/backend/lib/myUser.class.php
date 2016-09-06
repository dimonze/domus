<?php

class myUser extends sfBasicSecurityUser
{
  public function __get($attr) {    
    if ($this->isAuthenticated()) {
      return $this->getObject()->$attr;
    }
    return null;
  }

  public function getObject() {
    return $this->getAttribute('object', null);
  }
}
