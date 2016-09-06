<?php

/**
 * BaseUsersSearchFilterForm
 *
 * @package    domus
 * @subpackage filters
 * @author     Garin Studio
 * @version    
 */

class ApartamentsaleUsersSearchFilterForm extends BaseUsersSearchFilterForm
{
  protected $type = 'apartament-sale';
  
  protected function getAllowedFields() {
    return array( 1, 
        '54' => array(1, 2, 3, 4, 5, 'комната', 'квартира со свободной планировкой'),
        6, 5, '20' => array( 'подземная автостоянка', 'с отделкой' )
    );
  }
}

?>
