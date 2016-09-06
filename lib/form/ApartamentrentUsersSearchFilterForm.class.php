<?php

/**
 * BaseUsersSearchFilterForm
 *
 * @package    domus
 * @subpackage filters
 * @author     Garin Studio
 * @version    
 */

class ApartamentrentUsersSearchFilterForm extends BaseUsersSearchFilterForm
{
  protected $type = 'apartament-rent';
  
  protected function getAllowedFields() {
    return array( 1, 
        '55' => array(1, 2, 3, 4, 5, 'комната', 'квартира со свободной планировкой'),
        17, 18, 19,
        '21' => array( 'гараж/машиноместо' )
    );
  }
}

?>
