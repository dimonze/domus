<?php

/**
 * BaseUsersSearchFilterForm
 *
 * @package    domus
 * @subpackage filters
 * @author     Garin Studio
 * @version    
 */

class NewbuildingsaleUsersSearchFilterForm extends BaseUsersSearchFilterForm
{
  protected $type = 'new_building-sale';
  
  protected function getAllowedFields() {
    return array( 72, 76, 74, 75 );
  }
}

?>
