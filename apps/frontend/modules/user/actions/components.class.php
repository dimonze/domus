<?php

/**
 * user components.
 *
 * @package    domus
 * @subpackage user
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class userComponents extends sfActions
{
  public function executeSubmenu(sfWebRequest $request)
  {
    if ($this->getUser()->isAuthenticated()) {
      $query = Doctrine::getTable('Lot')->createQuery()
          ->select('status, count(id) nb')
          ->where('user_id = ?', $this->getUser()->id)
          ->groupBy('status');
      $this->lots = array();
      foreach ($query->fetchArray() as $row) {
        $this->lots[$row['status']] = $row['nb'];
      }

      $query = Doctrine::getTable('PM')->createQuery()
          ->select('red, count(id) nb')
          ->where('receiver = ?', $this->getUser()->id)
          ->groupBy('red');
      $this->messages = array('total' => 0);
      $total = 0;
      foreach ($query->fetchArray() as $row) {
        if ($row['red']){
          $total += $row['nb'];
        }
        else {
          $total = $row['nb'];
        }
        $this->messages[$row['red'] ? 'total' : 'unread'] = $total;
      }

      $query = Doctrine::getTable('Favourite')->createQuery()
          ->select ('count(lot_id) nb')
          ->where('user_id = ?', $this->getUser()->id)
          ->leftJoin('Favourite.Lot l')
          ->having('COUNT(l.id) > ?', 0)
          ->andWhere('l.deleted_at = ? OR l.deleted_at IS NULL', 0)
          ->fetchOne();

      $this->favourite = $query ? $query->nb : 0;

      $this->blog_posts = count(Doctrine::getTable('BlogPost')
        ->createQuery()
        ->andWhere('blog_id = ?', $this->getUser()->Blog->id)
      );
    }
  }
  public function executeRealtorrating()
  {
    $user = $this->getUser();
    $this->realtors = Doctrine::getTable('User')
      ->createQuery('u')
      ->select('u.id, u.name')
      ->leftJoin('u.Settings s with s.name = ? and s.value is null', 'show_rating')
      ->leftJoin('u.Regions r')
      ->andWhereIn('u.type', array('employee', 'realtor'))
      ->andWhere('u.photo IS NOT NULL')
      ->andWhere('r.region_id = ?', $user->current_region->id)
      ->andWhere('u.group_id != ? OR u.group_id IS NULL', UserGroup::PARTNERS_ID)
      ->having('count(s.user_id) = ?', 0)
      ->orderBy('u.rating desc')
      ->groupBy('u.id')
      ->limit(3)
      ->execute();
    $this->region = $user->current_region;

    if (count($this->realtors) == 0){
      $this->realtors = Doctrine::getTable('User')
      ->createQuery('u')
      ->select('u.id, u.name')
      ->leftJoin('u.Settings s with s.name = ? and s.value is null', 'show_rating')
      ->leftJoin('u.Regions r')
      ->andWhereIn('u.type', array('employee', 'realtor'))
      ->andWhere('u.photo IS NOT NULL')
      ->andWhere('r.region_id = ?', 77)
      ->andWhere('u.group_id != ? OR u.group_id IS NULL', UserGroup::PARTNERS_ID)
      ->having('count(s.user_id) = ?', 0)
      ->orderBy('u.rating desc')
      ->groupBy('u.id')
      ->limit(3)
      ->execute();
      $this->region = Doctrine::getTable('Region')->find(77);
    }
  }

  public function executeCompanyrating()
  {
    $user = $this->getUser();
    $this->companies =  Doctrine::getTable('User')
      ->createQuery('u')
      ->select('u.id, u.name')
      ->leftJoin('u.Settings s with s.name = ? and s.value is null', 'show_rating')
      ->leftJoin('u.Regions r')
      ->andWhere('u.type = ?', 'company')
      ->andWhere('u.photo IS NOT NULL')
      ->andWhere('r.region_id = ?', $user->current_region->id)
      ->andWhere('u.group_id != ? OR u.group_id IS NULL', UserGroup::PARTNERS_ID)
      ->having('count(s.user_id) = ?', 0)
      ->orderBy('u.rating desc')
      ->groupBy('u.id')
      ->limit(3)
      ->execute();
    $this->region = $user->current_region;

    if (count($this->companies) == 0){
      $this->companies =  Doctrine::getTable('User')
      ->createQuery('u')
      ->select('u.id, u.name')
      ->leftJoin('u.Settings s with s.name = ? and s.value is null', 'show_rating')
      ->leftJoin('u.Regions r')
      ->andWhere('u.type = ?', 'company')
      ->andWhere('u.photo IS NOT NULL')
      ->andWhere('r.region_id = ?', 77)
      ->andWhere('u.group_id != ? OR u.group_id IS NULL', UserGroup::PARTNERS_ID)
      ->having('count(s.user_id) = ?', 0)
      ->orderBy('u.rating desc')
      ->groupBy('u.id')
      ->limit(3)
      ->execute();
      $this->region = Doctrine::getTable('Region')->find(77);
    }
  }

  public function executeUserscount(sfWebRequest $request)
  {
    $user = $this->getUser();
    $cache = new DomusCache(array(
      'prefix'    => 'users_',
      'lifetime'  => 43000,
    ));

    $key = sprintf ('owners_count_%s', 'apartament-sale');
    if ($cache->has($key)){
      $this->count = unserialize($cache->get($key));
    }
    else {
      $users = Doctrine::getTable('User')->createQuery('u')
        ->select('COUNT(DISTINCT u.id) as count')
        ->leftJoin('u.Lot l')
        ->andWhere('u.type = ?', 'owner')
        ->fetchOne();

      $this->count = $users->count;
      $cache->set($key, serialize($this->count));
    }
  }
}
