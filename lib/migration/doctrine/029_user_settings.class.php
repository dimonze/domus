<?php

/**
 * @see #2101
 */

class UserSettings1Migration extends Doctrine_Migration
{
  public function up ()
  {
    $conn = Doctrine_Manager::getInstance()->connection();
    $users = $conn->getTable('User')->createQuery()->select('id')->orderBy('id')->fetchArray();
    foreach ($users as $user){
      $conn->execute('insert into user_settings (user_id, name, value) values (?, ?, ?) on duplicate key update value = ?',
             array($user['id'], 'send_email', 1, 1));
    }
  }
  
  public function down ()
  {

  }
}