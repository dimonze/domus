<?php

/**
 * currency actions.
 *
 * @package    domus
 * @subpackage currency
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class currencyActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $this->rates = Currency::getRates();
  }
}
