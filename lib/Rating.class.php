<?php

abstract class Rating {
  protected static $_config = null;
  /**
   * @param Doctrine_Record $object
   * @return mixed integer|null
   */
  public static function calculate (Doctrine_Record $object) {
    if ($object instanceOf Lot) {
      return self::calculateLot($object);
    }
    if ($object instanceOf User) {
      return self::calculateUser($object);
    }

    return null;
  }

  /**
   * @param Lot $lot
   * @return integer
   */
  public static function getLotRate (Lot $lot, $status = null) {
    $rates = self::getConfig('lot-rate');
    
    if (($status ? $status : $lot->status) != 'active') {
      return 0;
    }

    foreach ($rates as $rating => $rate) {
      if ($lot->rating <= $rating || $rating == 100) {
        return $rate;
      }
    }
  }

  /**
   * @param Lot $lot
   * @return integer
   */
  private static function calculateLot (Lot $lot) {
    $rates = self::getConfig('lot');
    $rating = 0;

    if (count($lot->images) > 0) {
      $rating += $rates['photo']['first'];
      $rating += $rates['photo']['other'] * (count($lot->images) - 1);
    }
    unset($rates['photo']);

    foreach ($rates as $param => $rate) {
      if ($lot->$param) {
        $rating += $rate;
      }
    }

    foreach ($lot->LotInfo as $info) {
      if ($info->value !== null) {
        $rating += $info->FormField->rating;
      }
    }

    return $rating;
  }

  /**
   * @param User $user
   * @return integer
   */
  private static function calculateUser (User $user) {
    if ($user->type == 'owner') {
      return null;
    }

    $rates = self::getConfig('user_' . $user->type);
    $rating = 0;

    foreach ($rates['info'] as $param => $rate) {
      if ($rate && $user->Info->$param) {
        $rating += $rate;
      }
    }
    unset($rates['info']);

    foreach ($rates as $param => $rate) {
      if ($user->$param) {
        $rating += $rate;
      }
    }

    if ($user->type == 'company') {
      $conn = Doctrine::getConnectionByTableName('user');
      $st = $conn->prepare('select sum(rating) from user where employer_id = ? and deleted = ?');
      $st->execute(array($user->id, 0));
      $rating += $st->fetchColumn();
    }
    
    return $rating;
  }

  /**
   * Return calculating rules
   * @param string $type
   * @return array
   */
  public static function getConfig ($type) {
    if (self::$_config === null) {
      self::$_config = sfYaml::load(sfConfig::get('sf_config_dir') . '/rating.yml');
    }

    $type = explode('_', $type);
    $config = self::$_config;
    foreach ($type as $path) {
      if (!isset($config[$path])) {
        $config = array();
        break;
      }
      $config = $config[$path];
    }
    
    return $config;
  }
}
