<?php

class rememberFilter extends sfFilter {
  public function execute($filterChain)
  {
    if ($this->isFirstCall()) {
      if (!$this->context->getUser()->isAuthenticated()) {
        if ($cookie = $this->context->getRequest()->getCookie('remember_me')) {
          if (!$this->context->getUser()->processLogin(array('remember' => true, 'key' => $cookie))) {
            $this->context->getResponse()->setCookie('remember_me', null);
          }
        }
      }
    }
    
    $filterChain->execute();
  }
}