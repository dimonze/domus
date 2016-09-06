<?php

/**
 * user components.
 *
 * @package    domus
 * @subpackage retargeting
 * @author     Garin Studio
 * @version    
 */
class retargetingComponents extends sfActions
{
  public function executeCode (sfWebRequest $request) 
  {
    $user = $this->getUser();
    $route = $this->context->getModuleName() . '/' . $this->context->getActionName();
    
    $routes_arr = sfConfig::get('app_retargeting_routes_array');
    
    if ($user->getFlash('retarget') || in_array($route, $routes_arr))
      $this->include_code = true;
    else
      $this->include_code = false;
  }
}

?>