<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class UserTable extends Doctrine_Table
{
  public function createQueryNbLots(Doctrine_Query $query)
  {
    return $query
      ->select('r.*')
      ->groupBy('r.id');
  }

  public function getModersIds() {
    $q = $this->createQuery('u')
      ->select('u.id')
      ->whereIn('u.group_id', array('5', '2'));

    return $q->fetchArray();
  }
}