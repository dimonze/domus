<?php

/**
 * @see #1507
 */

class UserType4Migration extends Doctrine_Migration
{
  public function up ()
  {
    $this->createForeignKey('user', array(
      'local' => 'employer_id',
      'foreign' => 'id',
      'foreignTable' => 'user',
      'name' => 'user_employer_id',
    ));
  }
  
  public function down ()
  {
    
  }
}