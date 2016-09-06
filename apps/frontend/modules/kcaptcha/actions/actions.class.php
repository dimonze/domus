<?php

class kcaptchaActions extends sfActions
{

  public function executeIndex(sfWebRequest $request) {
    
    $config = sfConfig::get('mod_kcaptcha_options', array());
    $kcaptcha = new KCaptcha($config);

    $this->getUser()->setAttribute('kcaptcha_key', $kcaptcha->generateKey());

    $response = $this->getResponse();

    $response->setContentType('image/png');
    $response->setContent($kcaptcha->render());

    $response->setHttpHeader('Cache-Control', 'no-cache');
    $response->setHttpHeader('Pragma', 'no-cache');

    return sfView::NONE;
  }

  public function executeQa(sfWebRequest $request) {
    $config = sfConfig::get('mod_kcaptcha_qa_options', array());
    $kcaptcha = new KCaptcha($config);

    $this->getUser()->setAttribute('kcaptcha_key', $kcaptcha->generateKey());

    $response = $this->getResponse();

    $response->setContentType('image/png');
    $response->setContent($kcaptcha->render());

    $response->setHttpHeader('Cache-Control', 'no-cache');
    $response->setHttpHeader('Pragma', 'no-cache');

    return sfView::NONE;
  }

}