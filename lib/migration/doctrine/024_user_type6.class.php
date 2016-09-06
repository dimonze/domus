<?php

/**
 * @see #2095
 */

class UserType6Migration extends Doctrine_Migration
{
  public function up ()
  {
    $conn = Doctrine_Manager::getInstance()->connection();
    $conn->execute('update user set type = ? where id = ?', array('source', 2));
    $conn->execute('update user set type = ? where id = ?', array('source', 3));
    $conn->execute('update user set type = ? where id = ?', array('source', 4));
    $conn->execute('update user set type = ? where id = ?', array('source', 5));
    $conn->execute('update user set type = ? where id = ?', array('source', 12));
  }
  
  public function down ()
  {

  }
}