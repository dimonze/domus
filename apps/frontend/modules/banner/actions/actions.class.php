<?php

/**
 * banner actions.
 *
 * @package    domus
 * @subpackage banner
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class bannerActions extends sfActions {

  public function executeFrame(sfWebRequest $request)
  {
    if ($request->getParameter('zones') && $request->getParameter('zones') < 30) {
      $this->zones = (int) $request->getParameter('zones');
    }
    else {
      $this->zones = 10;
    }
    $this->zone_id = 310;
    
    $align = trim($request->getParameter('align'));
    if (in_array($align, array('horizontal', 'vertical'))) {
      $this->align = $align;
    }
    else {
      $this->align = 'horizontal';
    }

    $targeting = trim($request->getParameter('target'));
    if (!empty($targeting)) {
      $this->zones = 3;
      $this->align = 'vertical';
      $this->target = $targeting;

      $this->setTemplate('frameTarget');
    }
    
    $this->setLayout(false);
  }

  public function executeFrameNaydiDom(sfWebRequest $request)
  {
    if ($request->getParameter('zones') && $request->getParameter('zones') < 30) {
      $this->zones = (int) $request->getParameter('zones');
    }
    else {
      $this->zones = 10;
    }
    $this->zone_id = 310;
    
    $align = trim($request->getParameter('align'));
    if (in_array($align, array('horizontal', 'vertical'))) {
      $this->align = $align;
    }
    else {
      $this->align = 'horizontal';
    }
    
    $this->setLayout(false);
  }

  public function executeHitBanner(sfWebRequest $request)
  {
    $this->setLayout(false);
    if (!$request->isXmlHttpRequest()) return $this->renderText(json_encode(array('status' => 'ok')));
    OpenX::hitClick();
    return $this->renderText(json_encode(array('status' => 'ok')));
  }
}