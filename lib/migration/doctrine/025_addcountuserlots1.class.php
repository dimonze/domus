<?php

/**
 * @see #2013
 */

class User1Migration extends Doctrine_Migration
{
  public function up ()
  {
    $this->addColumn('user', 'active_count', 'int', array('length' => 3));
    $this->addColumn('user', 'deleted_count', 'int', array('length' => 3));
  }
  
  public function down ()
  {
  }
}