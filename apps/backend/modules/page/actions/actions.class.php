<?php

require_once dirname(__FILE__).'/../lib/pageGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/pageGeneratorHelper.class.php';

/**
 * page actions.
 *
 * @package    domus
 * @subpackage page
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12474 2008-10-31 10:41:27Z fabien $
 */
class pageActions extends autoPageActions
{
  public function executeListUp(sfWebRequest $request) {
    $this->getRoute()->getObject()->moveUp();
    $this->redirect($request->getReferer());
  }

  public function executeListDown(sfWebRequest $request) {
    $this->getRoute()->getObject()->moveDown();
    $this->redirect($request->getReferer());
  }
}
