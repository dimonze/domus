<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class UserGroupTable extends Doctrine_Table
{
  public function createQueryNbUsers(Doctrine_Query $query)
  {
    return $query
      ->select('r.*, count(u.id) nb_users')
      ->leftJoin('r.Users u')
      ->groupBy('r.id');
  }
}