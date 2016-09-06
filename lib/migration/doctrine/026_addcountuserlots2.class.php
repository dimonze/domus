<?php

/**
 * @see #2013
 */

class User2Migration extends Doctrine_Migration
{
  public function up ()
  {
    $active_count = Doctrine::getTable('User')->createQuery('p')
      ->select('p.id, count(l.id) active_count')
      ->leftJoin('p.Lot l with l.deleted = 0 or l.deleted IS NULL')
      ->groupBy('p.id')
      ->fetchArray();
    $conn = Doctrine_Manager::getInstance()->connection();
    foreach ($active_count as $value)
    {
      $conn->execute('update user set active_count = ? where id = ?',
        array($value['active_count'], $value['id']));
    }

    $deleted_count = Doctrine::getTable('User')->createQuery('p')
      ->select('p.id, count(l.id) deleted_count')
      ->leftJoin('p.Lot l with l.deleted = 1')
      ->groupBy('p.id')
      ->fetchArray();
    $conn = Doctrine_Manager::getInstance()->connection();
    foreach ($deleted_count as $value)
    {
      $conn->execute('update user set deleted_count = ? where id = ?',
        array($value['deleted_count'], $value['id']));
    }
  }
  
  public function down ()
  {
  }
}