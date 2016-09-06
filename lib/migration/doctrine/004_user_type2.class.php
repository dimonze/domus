<?php

/**
 * @see #1507
 */

class UserType2Migration extends Doctrine_Migration
{
  public function up ()
  {
    $this->addColumn('user', 'company_name', 'string', array('length' => 100));
    $this->changeColumn('user', 'email', 'string', array(
      'length'  => 60,
      'notnull' => true,
    ));
    $this->changeColumn('user', 'password', 'string', array(
      'length'  => 32,
      'notnull' => true,
    ));
    $this->changeColumn('user', 'name', 'string', array(
      'length'  => 100,
      'notnull' => true,
    ));
    $this->changeColumn('user', 'phone', 'string', array(
      'length'  => 18,
      'notnull' => true,
    ));
  }
  
  public function down ()
  {
    $this->changeColumn('user', 'email', 'string', array('length' => 60));
    $this->changeColumn('user', 'password', 'string', array('length' => 32));
    $this->changeColumn('user', 'name', 'string', array('length' => 100));
    $this->changeColumn('user', 'phone', 'string', array('length' => 18));
    $this->removeColumn('user', 'company_name');
  }
}