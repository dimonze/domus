<?php

require_once dirname(__FILE__).'/../lib/claimGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/claimGeneratorHelper.class.php';

/**
 * claim actions.
 *
 * @package    domus
 * @subpackage claim
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12474 2008-10-31 10:41:27Z fabien $
 */
class claimActions extends autoClaimActions
{

  public function executeConfirm (sfWebRequest $request) {
    $claim = $this->getRoute()->getObject();
    $this->forward404Unless($claim);
    $claim->status = 'fixed';
    $claim->save();

    $this->redirect('claim_claim');
  }
}
