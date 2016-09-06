<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class LotStatisticTable extends Doctrine_Table
{
  public function createQueryStatistic($alias = 'l', $filters = array()) {        
    $query = parent::createQuery($alias);
    $query->select(
      $alias . '.stat_at, sum(' . $alias .'.active) as active, sum(' . 
      $alias . '.new) as new, sum(' . $alias . '.deleted) as deleted'
    );
    if (count($filters) > 0) {
      foreach ($filters as $name => $value) {
        switch ($name) {
          case 'type':
            $query->andWhere($alias . '.type = ?', $value);
            break;
          case 'region':
            $query->andWhere($alias . '.region_id = ?', $value);
            break;
          case 'date_from':
            $date = date('Y-m-d', strtotime($value));
            if ($filters['date_from'] == $filters['date_to']) {
              $query->andWhere($alias . '.stat_at like ?', $date . '%');
            }
            else {              
              $query->andWhere($alias. '.stat_at >= ?', array($date));
            }
            break;
          case 'date_to':            
            $date = date('Y-m-d', strtotime($value));
            if ($filters['date_from'] == $filters['date_to']) {
              $query->andWhere($alias . '.stat_at like ?', $date . '%');
            }
            else {              
              $query->andWhere($alias . '.stat_at <= ?', array($date));
            }
            break;
          case 'usertype1':
            $user_filters[] = 'company';
            break;
          case 'usertype2':
            $user_filters[] = 'employee';
            break;
          case 'usertype3':
            $user_filters[] = 'realtor';
            break;
          case 'usertype4':
            $user_filters[] = 'owner';
            break;
          case 'usertype5':
            $user_filters[] = 'source';
            break;
         }
      }
      if (count($user_filters) > 0) {
        $query->andWhereIn($alias . '.user_type', $user_filters);
      }
    }
    return $query->groupBy($alias . '.stat_at')
      ->orderBy($alias . '.stat_at');
  }
}