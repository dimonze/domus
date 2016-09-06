<?php
class sfGeoIpRuDoctrine extends sfGeoIpRu {
  public static function fetch ($ip) {
    $ip = self::convert($ip);
    $query = Doctrine::getTable('GeoIpRu')->createQuery();
    $query->where('start <= :ip and end >= :ip', array(':ip' => $ip));
    return $query->fetchOne();
  }
}