<?php

/**
 * Tools for parsing
 *
 * @author     Garin Studio
 */
abstract class ParseTools
{
  const
    RUB     = 'р',
    KV_M    = 'р/кв.м.',
    KV_M_G  = 'р/кв.м./год',
    MIN_S   = 8;  //минимальная площадь помещения

  private static $context = null;
  public static
    $filter = array(
      'apartament-sale'  => array(
        'calcs'                 => self::KV_M,
        'Москва'                => 50000,
        'Московская обл.'       => 30000,
        'Санкт-Петербург'       => 40000,
        'Ленинградская обл.'    => 25000,
        'Нижний Новгород'       => 30000,
        'Нижегородская обл.'    => 15000,
        'Калининград'           => 25000,
        'Калининградская обл.'  => 25000,
        'Краснодар'             => 30000,
        'Краснодарский край'    => 15000,
        'Волгоград'             => 30000,
        'Волгоградская обл.'    => 15000,
        'Ростов-на-Дону'        => 30000,
        'Ростовская обл.'       => 15000,
        'other'                 => 15000,
      ),
      'apartament-sale_room'  => array(
        'calcs'                 => self::RUB,
        'Москва'                => 900000,
        'Московская обл.'       => 300000,
        'Санкт-Петербург'       => 900000,
        'Ленинградская обл.'    => 300000,
        'Нижний Новгород'       => 400000,
        'Нижегородская обл.'    => 300000,
        'Калининград'           => 300000,
        'Калининградская обл.'  => 300000,
        'Краснодар'             => 400000,
        'Краснодарский край'    => 300000,
        'Волгоград'             => 400000,
        'Волгоградская обл.'    => 300000,
        'Ростов-на-Дону'        => 400000,
        'Ростовская обл.'       => 300000,
        'other'                 => 300000,
      ),
      'apartament-rent'  => array(
        'calcs'                 => self::RUB,
        'Москва'                => 15000,
        'Московская обл.'       => 15000,
        'Санкт-Петербург'       => 10000,
        'Ленинградская обл.'    => 10000,
        'Нижний Новгород'       => 6000,
        'Нижегородская обл.'    => 5000,
        'Калининград'           => 5000,
        'Калининградская обл.'  => 5000,
        'Краснодар'             => 6000,
        'Краснодарский край'    => 5000,
        'Волгоград'             => 6000,
        'Волгоградская обл.'    => 5000,
        'Ростов-на-Дону'        => 6000,
        'Ростовская обл.'       => 5000,
        'other'                 => 5000,
      ),
      'apartament-rent_room'  => array(
        'calcs'                 => self::RUB,
        'Москва'                => 5000,
        'Московская обл.'       => 5000,
        'Санкт-Петербург'       => 5000,
        'Ленинградская обл.'    => 5000,
        'Нижний Новгород'       => 3500,
        'Нижегородская обл.'    => 3000,
        'Калининград'           => 3000,
        'Калининградская обл.'  => 3000,
        'Краснодар'             => 3500,
        'Краснодарский край'    => 3000,
        'Волгоград'             => 3500,
        'Волгоградская обл.'    => 3000,
        'Ростов-на-Дону'        => 3500,
        'Ростовская обл.'       => 3000,
        'other'                 => 3000,
      ),
      'house-sale'  => array(
        'calcs'                 => self::RUB,
        'Москва'                => 900000,
        'Московская обл.'       => 900000,
        'Санкт-Петербург'       => 900000,
        'Ленинградская обл.'    => 900000,
        'Нижний Новгород'       => 150000,
        'Нижегородская обл.'    => 150000,
        'Калининград'           => 150000,
        'Калининградская обл.'  => 50000,
        'Краснодар'             => 150000,
        'Краснодарский край'    => 150000,
        'Волгоград'             => 150000,
        'Волгоградская обл.'    => 150000,
        'Ростов-на-Дону'        => 150000,
        'Ростовская обл.'       => 150000,
        'other'                 => 5000,
      ),
      'house-rent'  => array(
        'calcs'                 => self::RUB,
        'Москва'                => 20000,
        'Московская обл.'       => 20000,
        'Санкт-Петербург'       => 20000,
        'Ленинградская обл.'    => 20000,
        'Нижний Новгород'       => 10000,
        'Нижегородская обл.'    => 5000,
        'Калининград'           => 10000,
        'Калининградская обл.'  => 5000,
        'Краснодар'             => 10000,
        'Краснодарский край'    => 5000,
        'Волгоград'             => 10000,
        'Волгоградская обл.'    => 5000,
        'Ростов-на-Дону'        => 10000,
        'Ростовская обл.'       => 5000,
        'other'                 => 5000,
      ),
      'commercial-sale'  => array(
        'calcs'                 => self::KV_M,
        'Москва'                => array(50000, 600000),
        'Московская обл.'       => array(20000, 300000),
        'Санкт-Петербург'       => array(30000, 600000),
        'Ленинградская обл.'    => array(20000, 300000),
        'Нижний Новгород'       => 20000,
        'Нижегородская обл.'    => 20000,
        'Калининград'           => 20000,
        'Калининградская обл.'  => 20000,
        'Краснодар'             => 20000,
        'Краснодарский край'    => 20000,
        'Волгоград'             => 20000,
        'Волгоградская обл.'    => 20000,
        'Ростов-на-Дону'        => 20000,
        'Ростовская обл.'       => 20000,
        'other'                 => 20000,
      ),
      'commercial-rent'  => array(
        'calcs'                 => self::KV_M_G,
        'Москва'                => array(3000, 60000),
        'Московская обл.'       => array(2000, 60000),
        'Санкт-Петербург'       => array(3000, 60000),
        'Ленинградская обл.'    => array(2000, 60000),
        'Нижний Новгород'       => array(2000, 60000),
        'Нижегородская обл.'    => array(2000, 60000),
        'Калининград'           => array(2000, 60000),
        'Калининградская обл.'  => array(2000, 60000),
        'Краснодар'             => array(2000, 60000),
        'Краснодарский край'    => array(2000, 60000),
        'Волгоград'             => array(2000, 60000),
        'Волгоградская обл.'    => array(2000, 60000),
        'Ростов-на-Дону'        => array(2000, 60000),
        'Ростовская обл.'       => array(2000, 60000),
        'other'                 => array(2000, 60000),
      ),
    );

  public static function getStreamContext()
  {
    if (is_null(self::$context)) {
      $context_options = array(
        'http' => array(
          'proxy' => 'tcp://192.168.1.3:8192',
          'request_fulluri' => true,
        ),
      );

      self::$context = stream_context_create($context_options);
    }

     return self::$context;
  }

  public static function getLifetime($data)
  {
    if (mb_strpos($data['type'], 'sale')) return mt_rand(50, 70);
    else return mt_rand(25, 40);
  }

  public static function matchCommercialType($description)
  {
    if (mb_stripos($description, 'офис', null, 'utf-8') !== false
            || mb_stripos($description, 'бизнес', null, 'utf-8') !== false) {
      return 'Офис';
    }
    elseif (mb_stripos($description, 'склад', null, 'utf-8') !== false) {
      return 'Склад';
    }
    elseif (mb_stripos($description, 'торгов', null, 'utf-8') !== false) {
      return 'Торговые площади';
    }
    elseif (mb_stripos($description, 'здани', null, 'utf-8') !== false) {
      return 'Отд. стоящее здание';
    }
    elseif (mb_stripos($description, 'участ', null, 'utf-8') !== false
            || mb_stripos($description, 'земл', null, 'utf-8') !== false) {
      return 'Земля';
    }
    elseif (mb_stripos($description, 'ПСН', null, 'utf-8') !== false
            || mb_stripos($description, 'свобод', null, 'utf-8') !== false
            || mb_stripos($description, 'нежилое', null, 'utf-8') !== false
            || mb_stripos($description, 'разл.', null, 'utf-8') !== false) {
      return 'Свободного назначения';
    }
    elseif (mb_stripos($description, 'производ', null, 'utf-8') !== false) {
      return 'Производ. площади';
    }
    elseif (mb_stripos($description, 'спорт', null, 'utf-8') !== false) {
      return 'Спорт. назначения';
    }
    elseif (mb_stripos($description, 'развлек', null, 'utf-8') !== false) {
      return 'Развлекательный';
    }
    elseif (mb_stripos($description, 'здрав', null, 'utf-8') !== false) {
      return 'Объект здравоохранения';
    }
    elseif (mb_stripos($description, 'розн', null, 'utf-8') !== false
            || mb_stripos($description, 'сеть', null, 'utf-8') !== false) {
      return 'Розничная сеть';
    }
    elseif (mb_stripos($description, 'ресторан', null, 'utf-8') !== false
            || mb_stripos($description, 'кафе', null, 'utf-8') !== false
            || mb_stripos($description, 'клуб', null, 'utf-8') !== false) {
      return 'Ресторан/кафе';
    }
    elseif (mb_stripos($description, 'магаз', null, 'utf-8') !== false) {
      return 'Магазин';
    }
    elseif (mb_stripos($description, 'банк', null, 'utf-8') !== false) {
      return 'Банковское помещение';
    }
    elseif (mb_stripos($description, 'АЗС', null, 'utf-8') !== false) {
      return 'АЗС';
    }
    elseif (mb_stripos($description, 'гостиниц', null, 'utf-8') !== false
            || mb_stripos($description, 'отель', null, 'utf-8') !== false) {
      return 'Гостиница, мотель';
    }
    elseif (mb_stripos($description, 'завод', null, 'utf-8') !== false
            || mb_stripos($description, 'фабри', null, 'utf-8') !== false ) {
      return 'Завод, фабрика';
    }
    elseif (mb_stripos($description, 'груз', null, 'utf-8') !== false
            || mb_stripos($description, 'терминал', null, 'utf-8') !== false) {
      return 'Грузовой терминал';
    }
    elseif (mb_stripos($description, 'быт', null, 'utf-8') !== false
            || mb_stripos($description, 'парикмах', null, 'utf-8') !== false
            || mb_stripos($description, 'обслуж', null, 'utf-8') !== false) {
      return 'Объекты бытовых услуг';
    }
    else {
      return 'Другое';
    }
  }

  public static function getMinPrice($type, $region_id)
  {
    $regions = array(
      77  => 'Московская обл.',
      50  => 'Московская обл.',
      78  => 'Ленинградская обл.',
      47  => 'Ленинградская обл.',
      52  => 'Нижегородская обл.',
      520 => 'Нижегородская обл.',
      39  => 'Калининградская обл.',
      23  => 'Краснодарский край',
      61  => 'Ростовская обл.',
      34  => 'Волгоградская обл.',
    );

    if (!isset($regions[$region_id])) {
      $prices = self::$filter[$type]['other'];
    }
    else {
      $prices = self::$filter[$type][$regions[$region_id]];
    }

    if (self::$filter[$type]['calcs'] == self::KV_M) {
      if (is_array($prices)) {
        return $prices[0]*self::MIN_S;
      } else {
        return $prices*self::MIN_S;
      }
    } else {
      if (is_array($prices)) {
        return $prices[0];
      } else {
        return $prices;
      }
    }
  }

  public static function getRegionName($region_id)
  {
    switch ($region_id) {
      case 1:  return 'Адыгея';
      case 2:  return 'Башкортостан';
      case 3:  return 'Бурятия';
      case 4:  return 'Алтай';
      case 5:  return 'Дагестан';
      case 6:  return 'Ингушетия';
      case 7:  return 'Кабардино-Балкария';
      case 8:  return 'Калмыкия';
      case 9:  return 'Карачаево-Черкессия';
      case 10: return 'Карелия';
      case 11: return 'Коми';
      case 12: return 'Марий Эл';
      case 13: return 'Мордовия';
      case 14: return 'Саха /Якутия/';
      case 15: return 'Северная Осетия';
      case 16: return 'Татарстан';
      case 17: return 'Тыва';
      case 18: return 'Удмуртия';
      case 19: return 'Хакасия';
      case 20: return 'Чечня';
      case 21: return 'Чувашия';
      case 22: return 'Алтайский край';
      case 23: return 'Краснодарский край';
      case 24: return 'Красноярский край';
      case 25: return 'Приморский край';
      case 26: return 'Ставропольский край';
      case 27: return 'Хабаровский край';
      case 28: return 'Амурская обл.';
      case 29: return 'Архангельская обл.';
      case 30: return 'Астраханская обл.';
      case 31: return 'Белгородская обл.';
      case 32: return 'Брянская обл.';
      case 33: return 'Владимирская обл.';
      case 34: return 'Волгоградская обл.';
      case 35: return 'Вологодская обл.';
      case 36: return 'Воронежская обл.';
      case 37: return 'Ивановская обл.';
      case 38: return 'Иркутская обл.';
      case 39: return 'Калининградская обл.';
      case 40: return 'Калужская обл.';
      case 41: return 'Камчатский край';
      case 42: return 'Кемеровская обл.';
      case 43: return 'Кировская обл.';
      case 44: return 'Костромская обл.';
      case 45: return 'Курганская обл.';
      case 46: return 'Курская обл.';
      case 47: return 'Ленинградская обл.';
      case 48: return 'Липецкая обл.';
      case 49: return 'Магаданская обл.';
      case 50: return 'Московская обл.';
      case 51: return 'Мурманская обл.';
      case 52: return 'Нижегородская обл.';
      case 520: return 'Нижегородская обл.';
      case 53: return 'Новгородская обл.';
      case 54: return 'Новосибирская обл.';
      case 55: return 'Омская обл.';
      case 56: return 'Оренбургская обл.';
      case 57: return 'Орловская обл.';
      case 58: return 'Пензенская обл.';
      case 59: return 'Пермский край';
      case 60: return 'Псковская обл.';
      case 61: return 'Ростовская обл.';
      case 62: return 'Рязанская обл.';
      case 63: return 'Самарская обл.';
      case 64: return 'Саратовская обл.';
      case 65: return 'Сахалинская обл.';
      case 66: return 'Свердловская обл.';
      case 67: return 'Смоленская обл.';
      case 68: return 'Тамбовская обл.';
      case 69: return 'Тверская обл.';
      case 70: return 'Томская обл.';
      case 71: return 'Тульская обл.';
      case 72: return 'Тюменская обл.';
      case 73: return 'Ульяновская обл.';
      case 74: return 'Челябинская обл.';
      case 75: return 'Забайкальский край';
      case 76: return 'Ярославская обл.';
      case 77: return 'Москва';
      case 78: return 'Санкт-Петербург';
      case 79: return 'Еврейская обл.';
      case 83: return 'Ненецкий АО';
      case 86: return 'Ханты-Мансийский АО';
      case 87: return 'Чукотский АО';
      case 89: return 'Ямало-Ненецкий АО';
      default:
        $query = Doctrine::getTable('Region')->find($region_id);
        return preg_replace('/^г\.\s*/iu', '', $query->getName());
    }
  }

  public static function getBuiltYear($value)
  {
    $value = preg_replace('/\D/', '', $value);
    if ($value < 100) {
      $value = $value > date('y') ? sprintf('19%d', $value) : sprintf('20%d', $value);
    }
    if ($value < 1900) {
      $value = null;
    }

    return $value;
  }

  public static function doFilter($data, $params)
  {
    if ($data['type'] == 'commercial-rent' && isset($params[53])) {
      $data['price'] = $params[53];
    }

    switch ($data['type']) {
      case 'apartament-sale':
      case 'apartament-rent':
        $field = 1;
        break;
      case 'house-sale':
      case 'house-rent':
        $field = 26;
        break;
      case 'commercial-sale':
      case 'commercial-rent':
        ($params[45] == 'Земля') ? $field = 47 : $field = 46;
        break;
      case 'new_building-sale':
        $field = 72;
        break;
      default:
        $field = null;
    }
    if (!in_array($data['type'], array('new_building-sale', 'cottage-sale'))) {
      if (empty($params[$field]) || !($params[$field] > 0)) {
        ParseLogger::writeError($data['organization_link'], ParseLogger::EMPTY_AREA);
        return false;
      } else {
        $params[$field] = round($params[$field], 2);

        if ($field != 47 && $params[$field] < self::MIN_S) {
          ParseLogger::writeError($data['organization_link'], ParseLogger::BAD_AREA, $params[$field]);
          return false;
        }
      }
    }

    switch ($data['type']) {
      case 'apartament-sale':
        if (empty($params[54])) {
          ParseLogger::writeError($data['organization_link'], ParseLogger::ROOMS_NUM);
          return false;
        }
        $params[54] == 'комната' ? $room = '_room' : $room = '';
        break;
      case 'apartament-rent':
        if (empty($params[55])) {
          ParseLogger::writeError($data['organization_link'], ParseLogger::ROOMS_NUM);
          return false;
        }
        $params[55] == 'комната' ? $room = '_room' : $room = '';

        $params[68] = Lot::$currency_types['apartament-rent'][Lot::$currency_default_type['apartament-rent']];
        break;
      case 'commercial-rent':
        $params[69] = Lot::$currency_types['commercial-rent'][Lot::$currency_default_type['commercial-rent']];
      default:
        $room = '';
    }

    if (empty($data['address1']) || mb_strlen($data['address1']) < 5) {
      ParseLogger::writeError($data['organization_link'], ParseLogger::EMPTY_ADDRESS);
      return false;
    }
    /*if (mb_strpos($data['address1'], ',', null, 'utf-8') === false) {
      ParseLogger::writeError($data['organization_link'], ParseLogger::BAD_ADDRESS, $data['address1']);
      return false;
    }*/

    if (empty($data['address2']) || mb_strlen($data['address2']) < 3) {
      if (preg_match('/^(?:Москва|Санкт)/isu', $data['address1']) ||
          preg_match('/.+, (?:Нижний Новгород|Калининград|Краснодар|Волгоград|Ростов-на-Дону)/isu', $data['address1'])) {
        ParseLogger::writeError($data['organization_link'], ParseLogger::EMPTY_ADDRESS2);
        return false;
      }
    }

    if ($field != 47 && !in_array($data['type'], array('new_building-sale', 'cottage-sale'))) {
      if (!self::filterPrices($data, $params[$field], $room)) return false;
    }

    $params['field'] = $field;
    return $params;
  }

   /**
   * Filters lots by price criteria
   * @param array $data
   * @param float $area
   * @param string $type
   * @return bool
   */
  public static function filterPrices($data, $area, $type = '')
  {
    preg_match('/^([^,]+)(?:,|$)/isu', $data['address1'], $matches);
    if (empty($matches[1])) {
      ParseLogger::writeError($data['organization_link'], ParseLogger::MATCH_ERROR, $data['address1']);
      return false;
    }
    if (empty(self::$filter[$data['type'].$type][$matches[1]])) {
      $matches[1] = 'other';
      //ParseLogger::writeError($data['organization_link'], ParseLogger::REGION_UNKNOWN, $matches[1]);
      //return false;
    }

    $range = self::$filter[$data['type'].$type][$matches[1]];
    $calcs = self::$filter[$data['type'].$type]['calcs'];

    if (is_array($range)) {
      $min = $range[0];
      $max = $range[1];

      if ($calcs == self::KV_M) {
        if (($data['price']/$area) < $min || ($data['price']/$area) > $max) {
          ParseLogger::writeError($data['organization_link'], ParseLogger::PRICE_ERROR, round($data['price']/$area, 2).' < '.$min.' || '.round($data['price']/$area, 2).' > '.$max);
          return false;
        }
      } else {
        if ($data['price'] < $min || $data['price'] > $max) {
          ParseLogger::writeError($data['organization_link'], ParseLogger::PRICE_ERROR, $data['price'].' < '.$min.' || '.$data['price'].' > '.$max);
          return false;
        }
      }
    } else {
      if ($calcs == self::KV_M) {
        if (($data['price']/$area) < $range) {
          ParseLogger::writeError($data['organization_link'], ParseLogger::PRICE_ERROR, round($data['price']/$area, 2).' < '.$range);
          return false;
        }
      } else {
        if ($data['price'] < $range) {
          ParseLogger::writeError($data['organization_link'], ParseLogger::PRICE_ERROR, $data['price'].' < '.$range);
          return false;
        }
      }
    }

    return true;
  }


  public static function removeDublicates($data, $area1, $write2log = true)
  {
    $column = (mb_strlen($data['address2'], 'utf-8') > 6) ? 'address2' : 'address1';
    $srch_addr = preg_replace('/[,\.]/i',' ', $data[$column]);
    $srch_addr = preg_replace('/\s+/', ' ', $srch_addr);
    $tmp_array = explode(' ', $srch_addr);
    $srch_addr = '%';
    foreach ($tmp_array as $word) {
      if (mb_strlen($word, 'utf-8') < 3 && !preg_match('/\d/', $word)) continue;
      $srch_addr .= $word.'%';
    }

    if (mb_strlen($srch_addr, 'utf-8') < 7) return true;

    if (true) {
      $params = array(
        'region_id' => $data['region_id'],
        'type'      => $data['type'],
        'q'         => $srch_addr,
      );

      $sphinx = new DomusSphinxClient(array('limit' => 70));
      $sphinx->search($params);
      $res = $sphinx->getRes();

      if ($res['total_found'] == 0 || empty($res['matches'])) return true;

      $ids = array();
      foreach ($res['matches'] as $match) {
        $ids[] = $match['id'];
      }

      $query = Doctrine_Query::create(ProjectConfiguration::getActive()->getSlaveConnection())
              ->from('Lot l')
              ->whereIn('l.id', $ids);
    }
    else {
      $query = Doctrine_Query::create(ProjectConfiguration::getActive()->getSlaveConnection())
              ->from('Lot')
              ->andWhere('region_id = ?', $data['region_id'])
              ->andWhere('type = ?', $data['type'])
              ->andWhere('status = ?', 'active')
              ->andWhere($column.' LIKE ?', $srch_addr);
    }

    $sim_lots = $query->execute();
    $query->free();
    unset($query);

    if (!$sim_lots->count()) return true;

    $not_founded_newest = true;

    $date1 = strtotime($data['created_at']);
    $phones1 = explode(',', $data['organization_contact_phone']);
    $address1_1 = preg_replace('/[^a-zа-я-\d]/iu',' ', $data['address1']);
    $address1_1 = trim(preg_replace('/\s+/', ' ', $address1_1));
    $address1_2 = preg_replace('/[^a-zа-я-\d]/iu',' ', $data['address2']);
    $address1_2 = trim(preg_replace('/\s+/', ' ', $address1_2));

    foreach ($sim_lots as $id => $lot) {
      preg_match('/^(?:Площадь|Участок):(\d+\.*\d*)/isu', $lot->brief, $matches);
      if (empty($matches[1]) || floatval($area1) != floatval($matches[1])) continue;

      $phones2 = explode(',', $lot->organization_contact_phone);
      $sim_phone = false;
      foreach ($phones2 as $phone2) {
        $phone2 = preg_replace('/[^\d]/', '', $phone2);
        foreach ($phones1 as $phone1) {
          $phone1 = preg_replace('/[^\d]/', '', $phone1);
          if ($phone1 == $phone2) {
            $sim_phone = true;
            break 2;
          }
        }
      }
      if ($sim_phone === false) continue;

      $address2_1 = preg_replace('/[^a-zа-я-\d]/iu',' ', $lot->address1);
      $address2_1 = trim(preg_replace('/\s+/', ' ', $address2_1));
      $address2_2 = preg_replace('/[^a-zа-я-\d]/iu',' ', $lot->address2);
      $address2_2 = trim(preg_replace('/\s+/', ' ', $address2_2));

      similar_text($address1_1, $address2_1, $sim_addr1);
      similar_text($address1_2, $address2_2, $sim_addr2);

      if ($sim_addr1 > 98 && $sim_addr2 > 98) {
        if (strtotime($lot->created_at) > $date1) {
          $not_founded_newest = false;
          continue;
        }

        $info = $lot->id.'-'.
                $lot->address1.', '.
                $lot->address2.': '.
                $lot->price.', '.
                $lot->organization_contact_phone.' - '.
                $lot->organization_link;

        if ($write2log) {
          ParseLogger::writeInfo($data['organization_link'], ParseLogger::SIMILAR, $info);
        }

        $lot->deactivate()->delete();
      }
    }

    unset($sim_lots);

    return $not_founded_newest;
  }


  public static function handleAddress($data, $geodata)
  {
    if (empty($geodata['address1'])) {
      ParseLogger::writeError($data['organization_link'], ParseLogger::EMPTY_GADDRESS, $data['address1'].', '.$data['address2']);
      return false;
    } elseif ($data['region_id'] != 77 && $data['region_id'] != 78) {
      if (preg_match('/^Санкт-Петербург/iu', $geodata['address1'])) {
        $data['region_id'] = 78;
        $geodata['address1'] = preg_replace('/, Санкт-Петербург$/iu', '', $geodata['address1']);
      }
      if (mb_strpos($geodata['address1'], ',', null, 'utf-8') !== false && $geodata['address1'] != 'Москва, Москва') {
        $data['address1'] = $geodata['address1'];
      }

      // if (!empty($data['address2'])) {
        // $punkt = mb_substr($geodata['address1'], mb_strrpos($geodata['address1'], ',')+2, mb_strlen($geodata['address1']));
        // $punkt = preg_replace('/(^|\s)[^,]{0,4}(\s|$)/isu', '', $punkt);
        // $punkt = preg_replace('/\s+/isu', '[^,]*', $punkt);
        // $data['address2'] = preg_replace('/(^|\s)[^,]{0,4}'.$punkt.'\s*[^,]{0,4}(,\s*|$)/isu', '', $data['address2']);
        // $data['address2'] = preg_replace('/^\s*,\s*/', '', $data['address2']);
      // }
    }

    if (empty($geodata['address2']) && mb_strpos($data['type'], 'house') === false) {
      if (preg_match('/^(?:Москва|Санкт)/isu', $data['address1']) ||
          preg_match('/.+, (?:Нижний Новгород|Калининград)/isu', $data['address1']) /*||
          preg_match('/.+, (?:Краснодар|Ростов-на-Дону|Волгоград)/isu', $data['address1'])*/) {
        ParseLogger::writeError($data['organization_link'], ParseLogger::EMPTY_GADDRESS2, $data['address1'].', '.$data['address2']);
        return false;
      }
    }
    // elseif (!empty($geodata['address2'])) {
      // if (!empty($data['address2'])) {

        // preg_match('/^([^,]+?)(?:,|$)/isu', $geodata['address2'], $matches);
        // $geodata['address2'] = $matches[1];

        // $tmp_addr = $geodata['address2'];
        // $tmp_addr = preg_replace('/(\s|^)[а-яА-Я0-9]{1,3}-[а-яА-Я]{1,3}/isu', '', $tmp_addr);
        // $tmp_addr = preg_replace('/(\s|^)[а-яА-Я]+\.(\s|$)/isu', '', $tmp_addr);
        // $tmp_addr = preg_replace('/(\s|^)[а-яА-Я]{1,3}(\s|$)/isu', '', $tmp_addr);
        // if (mb_strpos($tmp_addr, ' ', null, 'utf-8') !== false) {
          // $to_cut = array('улица','проспект','площадь','аллея','проезд','переулок','бульвар');

          // foreach ($to_cut as $item) {
            // $tmp_addr = preg_replace(array('/(\s|^)'.$item.'(\s|$)/iu'), '', $tmp_addr);
          // }
        // }

        // if (mb_strlen($tmp_addr) > 2 && preg_match('/'.$tmp_addr.'/isu', $data['address2'])) {
          // $geodata['address2'] = preg_replace('/[^,]*'.$tmp_addr.'[^,]*/isu', $geodata['address2'], $data['address2']);
          // $geodata['address2'] = str_replace(',', ', ', $geodata['address2']);
          // $geodata['address2'] = preg_replace('/\s+/', ' ', $geodata['address2']);
        // } else {
          // $geodata['address2'] = $data['address2'];
        // }
      // }

      // $data['address2'] = preg_replace('/\sд(ом|\.|\s)\s*/isu', ' ', $geodata['address2']);
    // }

    // if (!empty($geodata['mkrraion'])) {
      // if (empty($data['address2'])) {
        // $data['address2'] = $geodata['mkrraion'];
      // } elseif (mb_stripos($data['address2'], preg_replace('/мкр[.\s]*/is', '', $geodata['mkrraion'])) === false) {
        // $data['address2'] = $geodata['mkrraion'].', '.$data['address2'];
      // }
    // }

    if (!self::checkRegion($data)) {
      ParseLogger::writeError($data['organization_link'], ParseLogger::REGION_ERROR, $data['address1']);
      return false;
    }

    return $data;
  }

  private static function checkRegion($data)
  {
    preg_match('/^([^,]+)(?:,|$)/isu', $data['address1'], $matches);
    if (empty($matches[1])) {
      ParseLogger::writeError($data['organization_link'], ParseLogger::MATCH_ERROR, $data['address1']);
      return false;
    }

    switch ($data['region_id']) {
      case 77: return (!strcasecmp($matches[1], 'Москва'));
      case 50: return (!strcasecmp($matches[1], 'Московская обл.') || !strcasecmp($matches[1], 'Московская область'));
      case 78: return (!strcasecmp($matches[1], 'Санкт-Петербург'));
      case 47: return (!strcasecmp($matches[1], 'Ленинградская обл.') || !strcasecmp($matches[1], 'Ленинградская область'));
      case 52:
      case 520: return (!strcasecmp($matches[1], 'Нижегородская обл.') || !strcasecmp($matches[1], 'Нижегородская область'));
      case 39: return (!strcasecmp($matches[1], 'Калининградская обл.') || !strcasecmp($matches[1], 'Калининградская область'));
      case 23: return (!strcasecmp($matches[1], 'Краснодарский край') || $matches[1] == 'Краснодарский Край');
      case 61: return (!strcasecmp($matches[1], 'Ростовская обл.') || !strcasecmp($matches[1], 'Ростовская область'));
      case 34: return (!strcasecmp($matches[1], 'Волгоградская обл.') || !strcasecmp($matches[1], 'Волгоградская область'));
      default: return false;
    }
  }


  public static function getLatLngByAddress($address)
  {
    $address = preg_replace('/,[^,]+(р-н|округ)/isu', '', $address);
    $address = preg_replace('/\s\([^)]+\)/isu', '', $address);
    $address = preg_replace('/"[^"]+"/isu', '', $address);
    $address = preg_replace('/, м\.\s*[^,]+/isu', '', $address);
    $address = preg_replace('/,[^,]*\sш\.\s*[^,]*(, \d+\s*км\.*)*$/isu', '', $address);

    $geodata = Geocoder::getPlacemark($address);

    if (!empty($geodata->geometry) && !empty($geodata->geometry->location)) {
      $data['lat'] = $geodata->geometry->location->lat;
      $data['lng'] = $geodata->geometry->location->lng;

      return $data;
    }
    else {
      return false;
    }
  }

  /**
   * Get coordinates
   * @param string $address
   * @return array|false
   */
  public static function getGeolocation($address)
  {
    $address = preg_replace('/,[^,]+(район|р-н|округ)/isu', '', $address);
    $address = preg_replace('/\s\([^)]+\)/isu', '', $address);
    $address = preg_replace('/"[^"]+"/isu', '', $address);
    $address = preg_replace('/, м\.\s*[^,]+/isu', '', $address);
    $address = preg_replace('/,[^,]*\sш\.\s*[^,]*(, \d+\s*км\.*)*$/isu', '', $address);

    $geodata = Geocoder::getPlacemark($address);

    if (!empty($geodata->geometry) && !empty($geodata->geometry->location)) {
      $data['lat'] = $geodata->geometry->location->lat;
      $data['lng'] = $geodata->geometry->location->lng;

      if (!empty($geodata->address_components)) {
        $data = array_merge($data, self::getAddressDetails($geodata));
      }

      return $data;
    } else {
      return false;
    }
  }

  /**
   * Get address data from coordinates
   * @param float $lat, $lng
   * @return string
   */
  public static function getAddressData($lat, $lng)
  {
    $geodata = Geocoder::getPlacemark($lat.','.$lng);

    if (!empty($geodata->address_components)) {
      return self::getAddressDetails($geodata);
    } else {
      return false;
    }
  }

  /**
   *
   * @param object $areainfo
   * @return array
   */
  protected static function getAddressDetails($geodata)
  {
    $data = array();
    $data['address1'] = '';

    if (!empty($geodata->AddressDetails->Country->Locality)) {
      $areainfo = $geodata->AddressDetails->Country;
    }
    elseif (!empty($geodata->AddressDetails->Country->AdministrativeArea)) {
      $areainfo = $geodata->AddressDetails->Country->AdministrativeArea;
    }

    //fetch address1
    if (!empty($areainfo->Locality->LocalityName)) {
      $data['address1'] = $areainfo->Locality->LocalityName;

      preg_match('/([^,]+ (?:область|край|республика){1}|(?:область|край|республика){1} [^,]+),/iu', $geodata->address, $matches);
      if (!empty($matches[1])) {
        $data['address1'] = trim($matches[1]).', '.$data['address1'];
      }
    }
    else {
      if (!empty($areainfo->AdministrativeAreaName)) {
        $obl = $areainfo->AdministrativeAreaName;
        $obl = preg_replace('/ область$/isu', ' обл.', $obl);
        $data['address1'] .= preg_replace('/город /isu', '', $obl);
      }
      if (!empty($areainfo->SubAdministrativeArea->SubAdministrativeAreaName)) {
        $raion = $areainfo->SubAdministrativeArea->SubAdministrativeAreaName;
        if (preg_match('/район/isu', $raion)) {
          $data['address1'] .= ', '.preg_replace('/ район/isu', ' р-н', $raion);
        }
      }
      if (!empty($areainfo->SubAdministrativeArea->Locality->LocalityName)) {
        $punkt = $areainfo->SubAdministrativeArea->Locality->LocalityName;
        $punkt = preg_replace(array('/(^|\s)город\s+/isu', '/(^|\s)деревня\s*/isu', '/(^|\s)поселок\s*/isu', '/(^|\s)село\s+/isu'), '', $punkt);
        $data['address1'] .= ', '.$punkt;
      }
      if (!empty($areainfo->Locality->LocalityName)) {
        $punkt = $areainfo->Locality->LocalityName;
        $punkt = preg_replace(array('/(^|\s)город\s+/isu', '/(^|\s)деревня\s*/isu', '/(^|\s)поселок\s*/isu', '/(^|\s)село\s*/isu'), '', $punkt);
        $data['address1'] .= ', '.$punkt;
      }
    }

    if (empty($data['address1'])) {
      $parts = explode(',', $geodata->address);
      for ($i=count($parts)-1; $i>=0; $i--) {
        if (preg_match('/ (?:область|край|республика)/iu', $parts[$i])) {
          $obl = trim(preg_replace('/ область$/iu', ' обл.', $parts[$i]));
        }
        elseif (trim($parts[$i]) != 'Россия') {
          $city = trim($parts[$i]);
          break;
        }
      }

      if (isset($obl, $city)) $data['address1'] = $obl.', '.$city;
      elseif (isset($obl))    $data['address1'] = $obl;
      elseif (isset($city))   $data['address1'] = $city;
    }


    //fetch address2
    if (!empty($areainfo->Locality->Thoroughfare->ThoroughfareName)) {
      $data['address2'] = $areainfo->Locality->Thoroughfare->ThoroughfareName;
    }
    elseif (!empty($areainfo->Thoroughfare->ThoroughfareName)) {
      $data['address2'] = $areainfo->Thoroughfare->ThoroughfareName;
    }
    elseif (!empty($areainfo->SubAdministrativeArea->Locality->DependentLocality->Thoroughfare->ThoroughfareName)) {
      $data['address2'] = $areainfo->SubAdministrativeArea->Locality->DependentLocality->Thoroughfare->ThoroughfareName;
    }
    elseif (!empty($areainfo->SubAdministrativeArea->Locality->Thoroughfare->ThoroughfareName)) {
      $data['address2'] = $areainfo->SubAdministrativeArea->Locality->Thoroughfare->ThoroughfareName;
    }

    if (!empty($data['address2'])) {
      $data['address2'] = preg_replace('/строение/iu', 'стр.', $data['address2']);
      $data['address2'] = preg_replace('/корпус/iu', 'корп.', $data['address2']);
    }


    return $data;
  }


  // http://blog.killtheradio.net/tricks-hacks/phps-preg-functions-dont-release-memory/
  public static function preg_clear_cache()
  {
    for ($i=1; $i<=4097; $i++) preg_match('/'.$i.'/', '1');
  }
}
