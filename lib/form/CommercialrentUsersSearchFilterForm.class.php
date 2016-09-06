<?php

/**
 * BaseUsersSearchFilterForm
 *
 * @package    domus
 * @subpackage filters
 * @author     Garin Studio
 * @version    
 */

class CommercialrentUsersSearchFilterForm extends BaseUsersSearchFilterForm
{
  protected $type = 'commercial-rent';
  
  protected function getAllowedFields() {
    return array( 45, 46, 47, 5 );
  }
}

?>
