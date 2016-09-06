<?php

/**
 * region actions.
 *
 * @package    domus
 * @subpackage region
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class regionActions extends sfActions
{
  public function executeSet(sfWebRequest $request) {
    if ($region = $this->getRoute()->getObject()) {
      $this->getUser()->current_region = $region;
      $this->getResponse()->setCookie('current_region', $region->id);
      $this->getUser()->setFlash('region_changed', 'Регион изменен на ' . $region->name);
    }
    $this->redirect($request->getReferer());
  }

}
