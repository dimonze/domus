<?php

/**
 * BaseUsersSearchFilterForm
 *
 * @package    domus
 * @subpackage filters
 * @author     Garin Studio
 * @version    
 */

class HousesaleUsersSearchFilterForm extends BaseUsersSearchFilterForm
{
  protected $type = 'house-sale';
  
  protected function getAllowedFields() {
    return array( 26, 27, 5, 28, 29, 64,
        '30' => array( 'нет', 'exclude' => true )
        ,'31' => array( 'нет', 'exclude' => true )
        ,'32' => array( 'нет', 'exclude' => true )
        ,'33' => array( 'нет', 'exclude' => true )
        ,'34' => array( 'нет', 'exclude' => true )
    );
  }
}

?>
