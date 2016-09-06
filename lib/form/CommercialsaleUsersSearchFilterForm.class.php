<?php

/**
 * BaseUsersSearchFilterForm
 *
 * @package    domus
 * @subpackage filters
 * @author     Garin Studio
 * @version    
 */

class CommercialsaleUsersSearchFilterForm extends BaseUsersSearchFilterForm
{
  protected $type = 'commercial-sale';
  
  protected function getAllowedFields() {
    return array( 45, 46, 47, 5 );
  }
}

?>
