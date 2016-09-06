<?php
class sfGeoIpRu {
  private static 
    $cache = array(),
    $orm   = 'doctrine';

  public static function find ($ip) {
    $ip = self::convert($ip);

    if (self::inCache($ip)) {
      return self::getFromCache($ip);
    }

    $result = self::fetch($ip);
    self::addToCache($ip, $result);
    return $result;
  }
  
  public static function fetch($ip) {
    if (self::$orm == 'doctrine') return self::fetchDoctrine($ip);
  }

  public static function fetchDoctrine ($ip) {
    $query = Doctrine::getTable('GeoIpRu')->createQuery();
    $query->where('start <= ? and end >= ?', array($ip, $ip));
    return $query->fetchOne();
  }

  public static function convert ($ip) {
    if (is_numeric($ip)) return $ip;
    if (! ip2long($ip)) throw new sfException('Invalid ip address: ' . $ip);
    return sprintf('%u', ip2long($ip));
  }

  public static function inCache ($ip) {
    return isset(self::$cache[self::convert($ip)]);
  }

  public static function getFromCache ($ip) {
    if (self::inCache($ip)) {
      return self::$cache[self::convert($ip)];
    }
    return null;
  }
  
  public static function addToCache ($ip, $value) {
    self::$cache[self::convert($ip)] = $value;
  }

}