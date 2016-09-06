<?php

/**
 * BaseUsersSearchFilterForm
 *
 * @package    domus
 * @subpackage filters
 * @author     Garin Studio
 * @version    
 */

class HouserentUsersSearchFilterForm extends BaseUsersSearchFilterForm
{
  protected $type = 'house-rent';
  
  protected function getAllowedFields() {
    return array( 26, 27, 5, 28, 61,
        '56' => array( 'нет', 'exclude' => true )
        ,'57' => array( 'нет', 'exclude' => true )
        ,'58' => array( 'нет', 'exclude' => true )
        ,'60' => array( 'нет', 'exclude' => true )
        ,'59' => array( 'нет', 'exclude' => true )
        , 18, 19 );
  }
}

?>
