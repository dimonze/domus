<?php

/**
 *
 * @package    symfony
 * @subpackage routing
 * @author     Eugeniy Belyaev
 * @version    SVN: $Id$
 */
class DomusSearchRoute extends sfRoute
{
  public static
    $translation_table = array(
      'region_id'            => 'r',
      'regionnode[]'         => 'rn',
      'currency'             => 'c',
      'currency_type'        => 'ct',
      'map-maximized'        => 'm',
      'restrict_region'      => 'rr',
      'location-type'        => 'l',
      'sort'                 => 's',
      'restore_advanced'     => 'ra',

      'latitude[from]'       => 'lf',
      'latitude[to]'         => 'lt',
      'longitude[from]'      => 'lgf',
      'longitude[to]'        => 'lgt',
      'zoom'                 => 'z',

      'price[from]'          => 'pf',
      'price[to]'            => 'pt',

      'field[1][from]'       => 'f1f',
      'field[1][to]'         => 'f1t',

      'field[5][from]'       => 'f5f',
      'field[5][to]'         => 'f5t',

      'field[26][from]'      => 'f26f',
      'field[26][to]'        => 'f26t',

      'field[27][from]'      => 'f27f',
      'field[27][to]'        => 'f27t',

      'field[46][from]'      => 'f46f',
      'field[46][to]'        => 'f46t',

      'field[47][from]'      => 'f47f',
      'field[47][to]'        => 'f47t',
        
      'field[72][from]'      => 'f72f',
      'field[73][to]'        => 'f73t',

      'field[75][from]'      => 'f75f',
      'field[75][to]'        => 'f75t',

      'field[54][or][]'      => 'f54',
      'field[55][or][]'      => 'f55',

      'field[45][orlike][]'  => 'f45',

      'field[20][]'          => 'f20',
      'field[21][]'          => 'f21',
      'field[74]'            => 'f74',

      'field[6]'             => 'f6',
      'field[17]'            => 'f17',
      'field[18]'            => 'f18',
      'field[19]'            => 'f19',
      'field[28]'            => 'f28',
      'field[29]'            => 'f29',
      'field[30]'            => 'f30',
      'field[31]'            => 'f31',
      'field[32]'            => 'f32',
      'field[33]'            => 'f33',
      'field[34]'            => 'f34',
      'field[56]'            => 'f56',
      'field[57]'            => 'f57',
      'field[58]'            => 'f58',
      'field[59]'            => 'f59',
      'field[60]'            => 'f60',
      'field[61]'            => 'f61',
      'field[64]'            => 'f64',
      'field[76][or][]'      => 'f76',
      
      'field[107][or][]'     => 'f107',
      'field[92][from]'      => 'f92f',
      'field[92][to]'        => 'f93t',
      'field[94][from]'      => 'f94f',
      'field[95][to]'        => 'f95t',
      'square[from]'         => 'sqf',
      'square[to]'           => 'sqt',
    ),

    $translit_table = array(
      ' ' => '+',
      'щ' => 'shh',

      '/' => '--',
      'ё' => 'yo',
      'ж' => 'zh',
      'ч' => 'ch',
      'ш' => 'sh',
      'ъ' => '__',
      'ы' => 'yi',
      'э' => 'e1',
      'ю' => 'yu',
      'я' => 'ya',
      'ь' => '_',

      'а' => 'a',
      'б' => 'b',
      'в' => 'v',
      'г' => 'g',
      'д' => 'd',
      'е' => 'e',
      'з' => 'z',
      'и' => 'i',
      'й' => 'j',
      'к' => 'k',
      'л' => 'l',
      'м' => 'm',
      'н' => 'n',
      'о' => 'o',
      'п' => 'p',
      'р' => 'r',
      'с' => 's',
      'т' => 't',
      'у' => 'u',
      'ф' => 'f',
      'х' => 'h',
      'ц' => 'c',

      'Щ' => 'SHH',

      'Ё' => 'YO',
      'Ж' => 'ZH',
      'Ч' => 'CH',
      'Ш' => 'SH',
      'Ы' => 'YI',
      'Э' => 'E1',
      'Ю' => 'YU',
      'Я' => 'YA',

      'А' => 'A',
      'Б' => 'B',
      'В' => 'V',
      'Г' => 'G',
      'Д' => 'D',
      'Е' => 'E',
      'З' => 'Z',
      'И' => 'I',
      'Й' => 'J',
      'К' => 'K',
      'Л' => 'L',
      'М' => 'M',
      'Н' => 'N',
      'О' => 'O',
      'П' => 'P',
      'Р' => 'R',
      'С' => 'S',
      'Т' => 'T',
      'У' => 'U',
      'Ф' => 'F',
      'Х' => 'H',
      'Ц' => 'C',
    ),

    $translit_fields = array(
      'regionnode[]',
      'field[54][or][]',
      'field[76][or][]',
      'field[55][or][]',
      'field[45][orlike][]',
      'field[45][or][]',
      'field[18]',
      'field[19]',
      'field[28]',
      'field[29]',
      'field[30]',
      'field[31]',
      'field[32]',
      'field[33]',
      'field[34]',
      'field[6]',
      'field[20][]',
      'field[74]',
      'field[64]',
      'field[107][or][]',
    ),

    $_str2replace = array(
      'обл.', 'г.'
    ),

    $_str_replacements = array(
      'область', ''
    ),

    $_object_types = array(
      'apartament' => array(
        'квартира',
        'комната',
        '1 комнатная квартира',
        '2-х комнатная квартира',
        '3-х комнатная квартира'
      ),
      'house'      => array(
        'дача',
        'коттедж/дом',
        '1/2 дома',
        '1/3 дома',
        '2/3 дома',
        '1/4 дома',
        'таунхаус',
        'особняк',
        'участок',
      ),
      'commercial' => array(
        'Автосервис',
        'АЗС',
        'Банковское помещение',
        'Бизнес-парк',
        'Бизнес-центр',
        'Гостиница / мотель',
        'Грузовой терминал',
        'Дом отдыха / пансионат',
        'Завод / фабрика',
        'Земля',
        'Логистический центр',
        'Магазин',
        'Объект здравоохранения',
        'Объекты бытовых услуг',
        'Отд. стоящее здание',
        'Офис',
        'Производ. площади',
        'Развлекательный',
        'Ресторан / кафе',
        'Розничная сеть',
        'Свободного назначения',
        'Склад',
        'Спорт. назначения',
        'Торговые площади',
        'Другое',
      ),
     'new_building' => array(),
     'cottage' => array(),
    );

  protected function parseStarParameter($star)
  {
    $tmp = explode('/', $star);
    $path = '';
    for ($i = 0, $max = count($tmp); $i < $max; $i += 2) {
      if (!empty($tmp[$i])) {
        $field = $this->translateFieldName($tmp[$i]);
        $value = isset($tmp[$i + 1]) ? $this->translateFieldValue($field, urldecode($tmp[$i + 1])) : 1;
        $path .= sprintf('%s=%s&', $field, $value);
      }
    }
    parse_str($path, $parameters);

    return $parameters;
  }

  private function translateFieldName($field)
  {
    if (false !== ($real = array_search($field, self::$translation_table))) {
      return $real;
    }
    else {
      return $field;
    }
  }

  private function translateFieldValue($field, $value)
  {
    if (in_array($field, self::$translit_fields)) {
      return strtr($value, array_flip(self::$translit_table));
    }
    else {
      return $value;
    }
  }

  public static function getRouteOfType($type, $object, $word = null, $cid = null)
  {
    $obj_t = get_class($object);
    $route = array();
    $query_params = array(
      'type' => $type,
      'l' => 'form',
      'cid' => ($cid ? $cid : '')
    );

    switch ($obj_t) {
      case 'Street':
        $query_params['region_id'] = $object->Regionnode->region_id;
        $query_params['q'] = $object->full_name;
        $rn_id = $object->Regionnode->id;
        $query_params['rn'] = strtr($object->Regionnode->full_name, DomusSearchRoute::$translit_table);
        break;
      case 'Region':
        $query_params['region_id'] = $object->id;
        unset($query_params['l'], $query_params['cid']);
        break;
      case 'Regionnode':
        $query_params['region_id'] = $object->region_id;
        $query_params['rn'] = strtr($object->full_name, DomusSearchRoute::$translit_table);
        $rn_id = $object->region_id;
        break;
    }

    if (!empty($rn_id) && in_array($rn_id, array(2295,2296)))
      unset($query_params['rn']);

    if ($type == 'commercial-sale' || $type == 'commercial-rent') {
      $commercial_types = Doctrine::getTable('FormField')->find(45)->getChoices(false);
      foreach ($commercial_types as $commercial_type) {
        if (null != $word) {
          $route = '';
          if (mb_stristr($word, strtolower($commercial_type))
              || (stristr($word, 'коммерч') && stristr($commercial_type, 'коммерч'))
              || (stristr($word, 'площад') && stristr($commercial_type, 'площад'))
              || (stristr($word, 'здан') && stristr($commercial_type, 'здан'))
              || (stristr($word, 'помещен') && stristr($commercial_type, 'помещен'))) {
            $query_params['f45'] = strtr($commercial_type, DomusSearchRoute::$translit_table);
            $route = '@search?' . http_build_query($query_params);
            break;
          }
        }
      }
      if ($route == '') {
        $route = '@search?' . http_build_query($query_params);
      }
    }
    else {
      if ($type == 'apartament-sale') {
        if (mb_stristr($word, 'однокомн')) {
          $query_params = $query_params + array('f54' =>  strtr('1 комнатная квартира', self::$translit_table));
        }
        else if (mb_stristr($word, 'двухкомн')) {
          $query_params = $query_params + array('f54' =>  strtr('2-х комнатная квартира', self::$translit_table));
        }
        else if (mb_stristr($word, 'трехкомн')) {
          $query_params = $query_params + array('f54' =>  strtr('3-х комнатная квартира', self::$translit_table));
        }
        else if (mb_stristr($word, 'четырехкомн')) {
          $query_params = $query_params + array('f54' =>  strtr('4-х комнатная квартира', self::$translit_table));
        }
        else if (mb_stristr($word, 'комнат')) {
          $query_params = $query_params + array('f54' =>  strtr('комната', self::$translit_table));
        }
      }
      if ($type == 'apartament-rent') {
        if (mb_stristr($word, 'однокомн')) {
          $query_params = $query_params + array('f55' =>  strtr('1 комнатная квартира', self::$translit_table));
        }
        else if (mb_stristr($word, 'двухкомн')) {
          $query_params = $query_params + array('f55' =>  strtr('2-х комнатная квартира', self::$translit_table));
        }
        else if (mb_stristr($word, 'трехкомн')) {
          $query_params = $query_params + array('f55' =>  strtr('3-х комнатная квартира', self::$translit_table));
        }
        else if (mb_stristr($word, 'четырехкомн')) {
          $query_params = $query_params + array('f55' =>  strtr('4-х комнатная квартира', self::$translit_table));
        }
        else if (mb_stristr($word, 'комнат')) {
          $query_params = $query_params + array('f55' =>  strtr('комната', self::$translit_table));
        }
      }
      $route = '@search?' . http_build_query($query_params);
    }

    return $route;
  }

  public static function prepareParrentSearchRoute($params = array())
  {
    if (count($params) > 0) {
      $query_string = array(
        'type'      =>  $params['type'],
        'region_id' =>  $params['region_id'],
        'l'         => 'form'
      );

      $query = http_build_query($query_string);
      return '@search?' . $query;
    }
    return false;
  }

  /**
   * Build route from array of parameters
   *
   * @param array $params query params
   * @return string
   */
  public static function buildRouteFromParams(array $params)
  {
    $params = self::flatArray($params);
    $p = array();
    foreach ($params as &$param) {
      if (!in_array($param[0], array('type', 'region_id'))) {
        $param[0] = preg_replace('/regionnode\[\d+\]/', 'regionnode[]', $param[0]);
        $param[0] = preg_replace('/\[or\]\[\d+\]/', '[or][]', $param[0]);
        $param[0] = preg_replace('/\[orlike\]\[\d+\]/', '[or][]', $param[0]);

        if (!empty($param[1]) && in_array($param[0], self::$translit_fields)) {
          $param[1] = strtr($param[1], self::$translit_table);
        }

        $param[0] = strtr($param[0], self::$translation_table);
      }
      $p[] = implode('=', $param);
    }
    return '@search?' . implode('&', $p);
  }
  
  public static function buildUrlForRedirect($params)
  {
    unset(
      $params['restore_custom'], $params['referrer'], $params['hash'],
      $params['module'], $params['action'], $params['page'], $params['location-type'],
      $params['current_type'], $params['current_url'], $params['zoom'],
      $params['restore_region'], $params['utm_source'],
      $params['utm_medium'], $params['utm_campaign'],
      $params['cid'], $params['q']
    );
    krsort($params);
    $params = self::flatArray($params);
    $slug = array();
    foreach ($params as &$param) {
      if (!in_array($param[0], array('type', 'region_id'))) {
        $param[0] = preg_replace('/regionnode\[\d+\]/', 'regionnode[]', $param[0]);
        $param[0] = preg_replace('/\[or\]\[\d+\]/', '[or][]', $param[0]);
        $param[0] = preg_replace('/\[orlike\]\[\d+\]/', '[or][]', $param[0]);

        if ($param[0] == 'regionnode[]') {
          $node = Regionnode::unformatName($param[1]);
          if (!empty($node)) {
            $param[1] = $node[0];
          }
        }
        if (!empty($param[1]) && in_array($param[0], self::$translit_fields)) {
          if (!preg_match('#^[a-z]+$#u', $param[1])) {
            $param[1] = strtolower(strtr($param[1], self::$translit_table));
          }
          
        }
        $param[0] = strtr($param[0], self::$translation_table);
        $slug[] = str_replace('+', '-', $param[1]);
      }
      else {
        if ('type' == $param[0]) {
          $type = Lot::getRoutingType($param[1]) . '/';
        }
        if ('region_id' == $param[0]) {
          $host = Toolkit::getGeoHostByRegionId($param[1], !empty($type), !empty($type));
          if(substr($host, -1) == '/') $host = substr($host, 0, -1);
        }
      }
    }
    return (isset($host) ? $host : '') . '/' . (!empty($type) ? $type : '') . implode('-', $slug);
  }

  public static function buildHashFromParams($params)
  {
    $hash = array();
    foreach(self::flatArray($params) as $param) {
      list($key,$value) = $param;
      $key = preg_replace(
        array('/regionnode\[\d+\]/', '/\[or\]\[\d+\]/', '/\[orlike\]\[\d+\]/'),
        array('regionnode[]',        '[or][]',          '[orlike][]'),
        $key);
      if(!empty($value) && in_array($key, self::$translit_fields)) {
        $value = strtr($value, self::$translit_table);
      }
      $key = strtr($key, self::$translation_table);
      $hash[] = sprintf('%s/%s', $key, $value);
    }
    return sprintf('#%s', implode('/', $hash));
  }

  public static function flatArray($array, $prefix = '')
  {
    $result = array();
    foreach ($array as $key => $value) {
      if ($prefix) {
        $k = $prefix . '[' . $key . ']';
      }
      else {
        $k = $key;
      }

      if (is_array($value)) {
        $result = array_merge($result, self::flatArray($value, $k));
      } else {
        $result[] = array($k, $value);
      }
    }
    return $result;
  }

  public static function extractParts($slug, $type) {
    $parts = array();
    $only_one_regionnode = true;
    //WTF? merge '-'?
    $table = array_merge(array_flip(self::$translit_table), array('-' => ' '));

    preg_match('/(apartament|house|commercial|new_building|cottage)-(sale|rent)$/', $type, $match);
    array_shift($match);
    list($type, $action) = $match;

    //Object type
    foreach(self::$_object_types[$type] as $otype) {
      $translated_otype = Toolkit::slugify(
        strtr($otype, array(
          '.'   => '',
          ' / ' => '-',
          '/'   => '-',
        ))
      );     

      if(strpos($slug, $translated_otype) === 0) {
        switch($type) {
          case 'apartament':
            switch ($translated_otype) {
              case 'kvartira':
                $id = $action == 'sale' ? 54 : 55;
                $parts['field'][$id]['or'] = array(
                  "1 комнатная квартира", "2-х комнатная квартира", "3-х комнатная квартира",
                  "4-х комнатная квартира", "5+-?и комнатная квартира","квартира со свободной планировкой",
                );
                break;
              case 'komnata':
                $id = $action == 'sale' ? 54 : 55;
                $parts['field'][$id]['or'] = array("комната");
                break;
              case '1-komnatnaya-kvartira':
                $id = $action == 'sale' ? 54 : 55;
                $parts['field'][$id]['or'] = array("1 комнатная квартира");
                break;
              case '2-h-komnatnaya-kvartira':
                $id = $action == 'sale' ? 54 : 55;
                $parts['field'][$id]['or'] = array("2-х комнатная квартира");
                break;
              case '3-h-komnatnaya-kvartira':
                $id = $action == 'sale' ? 54 : 55;
                $parts['field'][$id]['or'] = array("3-х комнатная квартира");
                break;
            }
          break;

          case 'house':
            $parts['field'][64] = $otype;
          break;

          case 'commercial':
            $parts['field'][45]['orlike'] = array($otype);
          break;
        }
        $slug = preg_replace('/' . $translated_otype . '[\-]?/', '', $slug);
      }
    }

    //Regionnode
    $regionnode = null;
    $query = Doctrine::getTable('Regionnode')->createQuery()->
      select('name, socr');
    $values = array();

    if(preg_match('/metro-(.*?)$/', $slug, $matches)) {
      $query->andWhere('socr = ? or socr = ?');
      $values = array_merge($values, array('м', 'м.'));
      $slug = $matches[1];
    }

    if(preg_match('/(c|s|sv|v|yuv|yu|yuz|z|sz)ao/', $slug, $matches)) {
      $district = array_shift($matches);
      $pid = Doctrine::getTable('Regionnode')->createQuery()->
        select('id')->where('name = ?')->execute(array(strtr($district, $table)), Doctrine::HYDRATE_SCALAR);
      $query->andWhere('parent = ?');
      $values[] = $pid[0]['Regionnode_id'];
      $meta_node = strtr(strtoupper($district), $table);
      $only_one_regionnode = false;
    }
    elseif(preg_match('/(.*?)-rajon/', $slug, $matches)) {
      $slug = strtr($matches[1], $table);
      $query->andWhere('name like ?');
      $values[] = $slug;
      $query->andWhere('socr = ?');
      $values[] = 'р-н';
    }
    else {
      $slug = strtr($slug, $table);
      $query->andWhere('name like ?');
      $values[] = str_replace(' ', '%', $slug);
    }

    $query->andWhere('region_id = ?');
    $values[] = Toolkit::getRegionId();



    $result = $query->execute($values, Doctrine::HYDRATE_ARRAY);

    $regionnodes = array();
    foreach($result as $a) {
      $regionnodes[] = Regionnode::formatName($a['name'], $a['socr']);
      if($only_one_regionnode) {
        break;
      }
    }
    if(!$only_one_regionnode && count($regionnodes)) {
      $regionnodes[] = $meta_node;
    }
    if(null !== $regionnodes) {
      $parts['regionnode'] = $regionnodes;
    }

    return $parts;
  }
}
