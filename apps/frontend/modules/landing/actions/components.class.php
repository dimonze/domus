<?php

/**
 * lot components.
 *
 * @package    domus
 * @subpackage lot
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class landingComponents extends sfComponents
{
  public function executeSearchBox()
  { 
    $params = array(
      'type'      =>  $this->getRequest()->getParameter('type'),
      'region_id' =>  $this->getuser()->current_region->id
    );
    $options = array(
      'limit'       => 3000,
      'maxmatches'  => 3000
    );
    
    $sphinx = new DomusSphinxClient($options);
    $lpages = $sphinx->ListLandingPages($params);

    if ($lpages['total'] > 0) {
      $this->lpages = $lpages['matches'];
    }
  }
  
  public function executeLandingPagesBox() {
    $options = array('limit' => 3000);
    
    $params = array(
      'type'      => $this->type,
      'region_id' => $this->region_id,
    );

    $sphinx = new DomusSphinxClient($options);
    $lpages = $sphinx->ListLandingPages($params);

    if ($lpages['total'] > 0) {
      $this->lpages = $lpages['matches'];
    }
  }
}