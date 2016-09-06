<?php

/**
 * @see #2095
 */

class UserType5Migration extends Doctrine_Migration
{
  public function up ()
  {
    $this->changeColumn('user', 'type', 'enum', array(
      'values'  => array(
        0 => 'owner',
        1 => 'company',
        2 => 'employee',
        3 => 'realtor',
        4 => 'source',
      ),
    ));
  }
  
  public function down ()
  {
    $this->changeColumn('user', 'type', 'enum', array(
      'values'  => array(
        0 => 'owner',
        1 => 'company',
        2 => 'employee',
        3 => 'realtor',
      ),
    ));
  }
}