<?php
/**
 * Разбора строкового адреса в массив
 *
 * @author Garin Studio
 */

if(!class_exists('AddressHelper')):
class AddressHelper {
  public $result = array(
    'region' => null,
    'region_node' => null,
    'metro' => array(),
    'city_region' => null,
    'street'      => null,
    'address'     => array(
      'house'      => null,
      'building'   => null,
      'structure'  => null
    )
  );

  function __construct($address = '')
  {
    if(!empty($address)) {
      $this->result = $this->parseAddress($address);
    }
  }

  public function isOk()
  {
    return true;
  }

  public function __toString()
  {
    return (string)$this->result;
  }

  public function parseAddress($address)
  {
    $address_info = $this->result;
    $address = preg_split('/,/', $address);

    while($part = array_shift($address)) {
      if(!isset($address_info['country']) && $this->isCountry($part)) {
        $address_info['country'] = $part;
        continue;
      }

      if(!isset($address_info['region'])) {
        $tmp = $this->isRegion($part, true);
        if($tmp['result']) {
          $address_info['region'] = $tmp['name'];
          continue;
        }
      }

      if(empty($address_info['region_node'])) {
        $tmp = $this->isArea($part, true);
        if($tmp['result']) {
          $address_info['region_node'] = $tmp['name'];
          continue;
        }
      }

      $tmp = $this->isMetro($part, true);
      if($tmp['result']) {
        $address_info['metro'][] = $tmp['name'];
        continue;
      }

      if(empty($address_info['city_region'])) {
        $tmp = $this->isCity($part, true);
        if($tmp['result']) {
          $address_info['city_region'] = $tmp['name'];
          continue;
        }
      }

      if(empty($address_info['street'])) {
        $tmp = $this->isStreet($part, true);
        if($tmp['result']) {
          $address_info['street'] = $tmp['name'];
          continue;
        }
      }

      $tmp = $this->isShosse($part, true);
      if($tmp['result']) {
        $address_info['shosse'][] = $tmp['name'];
        continue;
      }

      if(empty($address_info['address']['house'])) {
        $tmp = $this->parseStreetNumber($part);
        if(is_array($tmp)) {
          $address_info['address'] = $tmp;
          continue;
        }
      }

      if(empty($address_info['address']['structure'])) {
        $tmp = $this->isStructure($part, true);
        if($tmp['result']) {
          $address_info['address']['structure'] = $tmp['name'];
          continue;
        }
      }

      if(empty($address_info['address']['building'])) {
        $tmp = $this->isBuilding($part, true);
        if($tmp['result']) {
          $address_info['address']['building'] = $tmp['name'];
          continue;
        }
      }

      $address_info['undefined'][] = trim($part);
    }


    if(empty($address_info['region'])) {
      if(!empty($address_info['undefined'])) {
        foreach ($address_info['undefined'] as $i => $un) {
          if(preg_match('/^[а-яёЁ\-\s]+$/iu', $un) && mb_strlen($un) > 3) {
            if($region_id = $this->fetchRegionId($un)) {
              $address_info['region'] = $region_id;
              unset($address_info['undefined'][$i]);
              break;
            }
          }
        }
      }
    }
    else {
      $address_info['region'] = $this->fetchRegionId($address_info['region']);
    }

    if(empty($address_info['region'])) {
      return $address_info;
    }
    elseif($address_info['region'] == 77 && empty($address_info['city_region'])) {
      $address_info['city_region'] = 'Москва';
    }
    elseif($address_info['region'] == 78 && empty($address_info['city_region'])) {
      $address_info['city_region'] = 'Санкт-Петербург';
    }


    if(empty($address_info['region_node'])) {
      if(!empty($address_info['undefined'])) {
        foreach ($address_info['undefined'] as $i => $un) {
          if(preg_match('/^[а-яёЁ\-\s]+$/iu', $un) && mb_strlen($un) > 3) {
            if($region_node_id = $this->fetchRegionNodeId($un, $address_info['region'])) {
              $address_info['region_node'] = $region_node_id;
              //unset($address_info['undefined'][$i]);
              break;
            }
          }
        }
      }
    }
    else {
      $address_info['region_node'] = $this->fetchRegionNodeId($address_info['region_node'], $address_info['region']);
    }


    if(empty($address_info['city_region'])) {
      if(!empty($address_info['undefined'])) {
        foreach ($address_info['undefined'] as $i => $un) {
          if(preg_match('/^[а-яёЁ\-\s]+$/iu', $un) && mb_strlen($un) > 3) {
            if($city_region_id = $this->fetchCityRegionId($un, $address_info['region'], $address_info['region_node'])) {
              $address_info['city_region'] = $city_region_id;
              unset($address_info['undefined'][$i]);
              break;
            }
          }
        }
      }
    }
    else {
      if($city_region_id = $this->fetchCityRegionId($address_info['city_region'], $address_info['region'], $address_info['region_node'])) {
        $address_info['city_region'] = $city_region_id;
      }
    }


    $address_info['region_node'] = empty($address_info['region_node']) ? array() : array($address_info['region_node']);
    if (!empty($address_info['city_region']) && is_numeric($address_info['city_region'])) {
      $address_info['region_node'][] = $address_info['city_region'];
      $address_info['city_region'] = null;
    }


    if(!empty($address_info['shosse'])){
      $address_info['region_node'] = array_merge($address_info['region_node'], $this->fetchShosseId($address_info['shosse'], $address_info['region']));
    }
    unset($address_info['shosse']);

    if(!empty($address_info['metro'])){
      $address_info['region_node'] = array_merge($address_info['region_node'], $this->fetchMetroId($address_info['metro'], $address_info['region']));
    }
    unset($address_info['metro']);

    if(empty($address_info['undefined'])) unset($address_info['undefined']);

    return $address_info;
  }

  //Проверка страны
  private function isCountry($data)
  {
    $res = 0;
    $data = preg_replace(array('/(Р|р)о[cс]+ия/', '/(У|у)кра(и|й)+на/'), '', $data, -1, $res);

    return $res;
  }

  //Проверка региона
  private function isRegion($data, $clear = false)
  {
    preg_match('/(област(?:ь|и)|обл\.?$|край|республика|автономный округ|Москва|С(?:анкт)*(?:\-|\s)Петербург|Башкортостан|Карелия|Чувашия)/iu', $data, $matches);
    if (empty($matches[1])) {
      return $clear ? array('result' => 0, 'name' => trim($data)) : 0;
    }
    elseif (!$clear) {
      return 1;
    }
    else {
      if (preg_match('/С(?:анкт)*(?:\-|\s)Петербург/iu', $matches[1])) {
        return array('result' => 1, 'name' => 'Санкт-Петербург');
      }
      $data = preg_replace('/(област(ь|и)|обл\.?$|\sкрай|республика|автономный округ)/iu', '', $data);
      return array('result' => 1, 'name' => trim($data));
    }
  }

  //Проверка района
  private function isArea($data, $clear = false)
  {
    $res = 0;
    $data = preg_replace(array('/район/iu', '/городской округ/iu', '/(^|\s)р\-н/iu'), '', $data, -1, $res);
    return $clear ? array('result' => $res, 'name' => trim($data)) : $res;
  }

  //Проверка метро
  private function isMetro($data, $clear = false)
  {
    $res = 0;
    $data = preg_replace(
    array('/метро/u', '/^м\.*\s+|\s+м\.*$/u'), '', trim($data), -1, $res);
    return $clear ? array('result' => $res, 'name' => trim($data)) : $res;
  }

  //Проверка населенного пункта
  private function isCity($data, $clear = false)
  {
    $res = 0;
    $data = preg_replace(array(
      '/поселок подсобного хозяйства/i',
      '/поселок (городского|сельского) типа/i',
      '/(городской|коттеджный|рабочий) пос(е|ё)лок/i',
      '/пос(е|ё)лок (альплагерь |станции |кордон )?/i',
      '/(^|\s)п(ос)*(?:\.|\s)/iu',
      '/(^|\s)(деревня|(д(ер)*\.(?!\s*\d)))/iu',
      '/(^|\s)село/i',
      '/колхоз/i',
      '/садовое товарищество/i',
      '/садоводство/i',
      '/(^|\s)(город\s|г\.|пгт\.*)/iu',
      '/при ж\/д станции/i',
      '/ж\/д станция/i',
      '/(^|\s)станция/i',
      '/(^|\s)хутор/i',
      '/станица/i'
    ), '', trim($data), -1, $res);

    return $clear ? array('result' => $res, 'name' => trim($data)) : $res;
  }

  //Проверка улицы
  private function isStreet($data, $clear = false)
  {
    $res = 0;
    $data = preg_replace(array(
      '/улица/i',
      '/(^|\s)ул\.*/i',
      '/(^|\s)у\-а/i',
      '/переулок/i',
      '/(^|\s)п\-к/i',
      '/проезд/i',
      '/(^|\s)п(р)*\-д/i',
      '/проспект/i',
      '/п(р)*\-(к)*т\.*/i',
      '/^пе?р\.*\s+|\s+пе?р\.*$/i',
      '/бульвар/i',
      '/(^|\s)б\-р/i',
      '/площадь/i',
      '/(^|\s)п(л|р)\.*/i',
      '/(^|\s)п\-дь/i',
    ), '', trim($data), -1, $res);

    return $clear ? array('result' => $res, 'name' => trim($data)) : $res;
  }

  //Проверка шоссе
  private function isShosse($data, $clear = false)
  {
    $res = 0;
    $data = preg_replace(array(
      '/шо[с]+е/i',
      '/(^|\s)ш\-е/i',
      '/(^|\s)ш(\.|\s|$)/i',
    ), '', trim($data), -1, $res);

    return $clear ? array('result' => $res, 'name' => trim($data)) : $res;
  }

  //Проверка корпуса
  private function isBuilding($data, $clear = false)
  {
    $res = 0;
    $data = preg_replace(array(
      '/(^|\s)корп./i',
      '/корпус/i',
    ), '', trim($data), -1, $res);

    return $clear ? array('result' => $res, 'name' => trim($data)) : $res;
  }

  //Проверка строения
  private function isStructure($data, $clear = false)
  {
    $res = 0;
    $data = preg_replace(array(
      '/(^|\s)стр\./i',
      '/строение/i',
    ), '', trim($data), -1, $res);

    return $clear ? array('result' => $res, 'name' => trim($data)) : $res;
  }

  private function parseStreetNumber($data)
  {
    $result = false;

    $data = preg_replace(array('/д\.+/', '/дом/'), '', $data);
    if(!$this->isStreet($data) && !preg_match('/\d+\s+км(?:\.|\s|$)/i', $data) && preg_match('/^(\d+|\d+к[^\s+]\d+)/iu', trim($data))) {
      $data = preg_split('/(корпус|корп|к|\/)[\.]*(\s|$)/iu', $data);

      if(count($data)) {
        $data = array_map('trim', $data);
        $result['house'] = !strlen($data[0]) && isset($data[1]) ? 'к' . $data[1] : preg_replace('/\s*\(.*?\)\s*/', '', $data[0]);
        $result['building'] = strlen($data[0]) && isset($data[1]) ? $data[1] : null;
        $result['structure'] = null;
      }
    }

    return $result;
  }


  private function fetchRegionId($region_name)
  {
    $query = Doctrine_Manager::connection()->prepare('
      SELECT `id`
      FROM `region`
      WHERE `name` LIKE ?
      LIMIT 3
    ');

    $query->execute(array('%'.$region_name.'%'));
    $data = $query->fetchAll(PDO::FETCH_COLUMN);
    $query->closeCursor();

    if(count($data) == 1) {
      return $data[0];
    }

    return null;
  }

  private function fetchRegionNodeId($node_name, $region_id)
  {
    $query = Doctrine_Manager::connection()->prepare('
      SELECT `id`
      FROM `regionnode`
      WHERE `region_id` = ? AND `name` = ? AND `has_children` = ?
      LIMIT 3
    ');

    $query->execute(array($region_id, $node_name, 1));
    $data = $query->fetchAll(PDO::FETCH_COLUMN);
    $query->closeCursor();

    if(count($data) == 1) {
      return $data[0];
    }

    return null;
  }

  private function fetchCityRegionId($city_name, $region_id, $region_node_id = null)
  {
    $query = Doctrine_Manager::connection()->prepare('
      SELECT `id`
      FROM `regionnode`
      WHERE `region_id` = ? AND `name` = ? AND `has_children` = ?
        '.(!empty($region_node_id) ? ' AND (`parent` = ? OR `parent` IS NULL)' : '').'
      LIMIT 3
    ');

    $params = array($region_id, $city_name, 0);
    if(!empty($region_node_id)) $params[] = $region_node_id;

    $query->execute($params);
    $data = $query->fetchAll(PDO::FETCH_COLUMN);
    $query->closeCursor();

    if(count($data) == 1) {
      return $data[0];
    }

    return null;
  }

  private function fetchShosseId($shosse_names, $region_id)
  {
    $query = Doctrine_Manager::connection()->prepare('
      SELECT `id`
      FROM `regionnode`
      WHERE `name` IN ('.implode(',', array_fill(0, count($shosse_names), '?')).') AND `region_id` = ? AND `socr` IN (?,?)
    ');

    $params = array_merge($shosse_names, array($region_id, 'ш.', 'ш'));

    $query->execute($params);
    $shosse_ids = $query->fetchAll(PDO::FETCH_COLUMN);
    $query->closeCursor();

    return $shosse_ids;
  }

  private function fetchMetroId($metro_names, $region_id)
  {
    $query = Doctrine_Manager::connection()->prepare('
      SELECT `id`
      FROM `regionnode`
      WHERE `name` IN ('.implode(',', array_fill(0, count($metro_names), '?')).') AND `region_id` = ? AND `socr` IN (?,?)
    ');

    foreach ($metro_names as &$metro) {
      $metro = preg_replace('/(пр\-т|просп\.*)(?:\s|$)/iu', 'проспект', $metro);
    }
    $params = array_merge($metro_names, array($region_id, 'м.', 'м'));

    $query->execute($params);
    $metro_ids = $query->fetchAll(PDO::FETCH_COLUMN);
    $query->closeCursor();

    return $metro_ids;
  }
}
endif;
?>
