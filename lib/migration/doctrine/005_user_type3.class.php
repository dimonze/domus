<?php

/**
 * @see #1507
 */

class UserType3Migration extends Doctrine_Migration
{
  public function up ()
  {
    $this->addColumn('user', 'employer_id', 'integer', array(
      'length' => 4,
      'unsigned' => true,
    ));
  }
  
  public function down ()
  {
    $this->removeColumn('user', 'employer_id');
  }
}