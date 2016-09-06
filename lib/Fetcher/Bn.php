<?php

require dirname(__FILE__).'/../helper/WordHelper.php';

/**
 * Class for fetching http://www.bn.ru/ lots
 *
 * @author Grechko Aleksey
 */
class Fetcher_Bn extends Fetcher
{
  public
    $lots_parsed = 0,
    $lots_fetched = 0;
  private
    $conn = null,
    $limit = null,
    $pages = array(),
    $pages_fetched = array(),
    $lots = array(),
    $lots_data = array(),
    $lot_options = array(),

    $exists_exceptions = 0,
    $exists_exceptions_limit = 100;

  /**
   * Constructor
   * @param array $options
   * @param callable $progress_callable = null
   * @return Fetcher_Dmir
   */
  public function __construct($options) {
    $this->limit = $options['limit'];
    $this->pages[] = $options['url'];
    $this->lot_options = $options['data'];
    $this->conn = ProjectConfiguration::getActive()->getSlaveConnection();

    return $this;
  }

  /**
   * Main function
   * @return void
   */
  public function get() {
    $this->getLocations();
    $this->lots_fetched = count($this->lots);
    ParseLogger::writeStart($this->lots_fetched);

    if ($this->lots_fetched) {
      $context_options = array(
        'http' => array(
            'proxy' => 'tcp://188.72.68.74:8192',
            'request_fulluri' => true,
            ),
        );
      $context = stream_context_create($context_options);

      foreach ($this->lots as $i => $url) {
        $this->progress(sprintf('Importing lot %d/%d.', $i+1, count($this->lots)));
        $need_delete = false;
        try {
          $data = $this->importLot($url, $i);
          if (!$data || empty($data['organization_link'])) continue;

          if ($this->lot_options['region_id'] != $data['region_id']) {
            $data['region_id'] = $this->lot_options['region_id'];
            if ($this->lot_options['region_id'] == 50) {
              $this->lot_options['region_id'] = 77;
            } elseif ($this->lot_options['region_id'] == 47) {
              $this->lot_options['region_id'] = 78;
            }
          }
          if ($data['status'] != 'active') {
            ParseLogger::writeError($url, ParseLogger::BAD_STATUS);
            continue;
          }

          if (!empty($data['params'])) {
            $params = $data['params'];
            unset($data['params']);
          } else {
            ParseLogger::writeError($url, ParseLogger::EMPTY_PARAMS);
            continue;
          }
          if (!empty($data['photos'])) {
            $photos = $data['photos'];
            unset($data['photos']);
          } else {
            $photos = array();
          }

          $params = ParseTools::doFilter($data, $params);
          if (!$params) continue;
          
          if ($data['type'] == 'commercial-rent') {
            $data['price'] = $params[53];
          }
          $field = $params['field'];
          unset($params['field']);
          
          if (empty($data['latitude']) || empty($data['longitude'])) {
            $address = $data['address1'].', '.$data['address2'];
            if (empty($data['address2']) || (!$geodata = ParseTools::getGeolocation($address))) {
              $address = $data['address1'];
              if (!$geodata = ParseTools::getGeolocation($address)) {
                ParseLogger::writeError($url, ParseLogger::EMPTY_GEODATA, $address);
                continue;
              }
            }

            $data['latitude']  = $geodata['lat'];
            $data['longitude'] = $geodata['lng'];
          } else {
            $geodata = ParseTools::getAddressData($data['latitude'], $data['longitude']);

            if ($data['region_id'] != 77 && $data['region_id'] != 78 && empty($geodata['address1'])) {
              $address = $data['address1'].', '.$data['address2'];
              if (empty($data['address2']) || !$geodata = ParseTools::getGeolocation($address)) {
              $address = $data['address1'];
                if (!$geodata = ParseTools::getGeolocation($address)) {
                  ParseLogger::writeError($url, ParseLogger::EMPTY_GEODATA, $address);
                  continue;
                }
              }
            }
          }

          $data = ParseTools::handleAddress($data, $geodata);
          if (!$data) continue;

          if (!ParseTools::removeDublicates($data, $params[$field])) {
            ParseLogger::writeError($url, ParseLogger::NEWER_EXISTS);
            continue;
          }

          $brief = array('type' => $data['type']);
          foreach ($params as $id => $param) {
            $brief["field$id"] = $param;
          }
          $data['brief'] = @DynamicForm::makeBrief($brief);

          $lot = new Lot();
          $lot->fromArray($data);
          $lot->save();

          $need_delete = true;
          foreach ($params as $id => $param) {
            $info = new LotInfo();
            $info->fromArray(array(
                'lot_id' => $lot->id,
                'field_id' => $id,
                'value' => $param
              ));
            $info->save();
            
            $info->free();
            unset($info);
          }
          
          $lot->save();
          $lot->free();
          unset($lot, $data, $params);
          
          $this->lots_parsed++;
        }
        catch (Exception $e) {
          ParseLogger::writeError($url, ParseLogger::EXCEPTION, $e->getMessage());
          if ($need_delete) {
            try {
              $lot->delete();
              $lot->free();
              unset($lot, $data, $params);
            }
            catch (Exception $e) {}
          }
          if ($e->getMessage() == 'Geocoder limit reached') return;

          continue;
        }
      }
    }
  }

  protected function importLot($url, $n) {
    try {
      $html = $this->fetch($url, array(
          'strip_comments'      => true,
          'strip_html'          => true,
          'strip_html_options'  => array('script'),
          'only_body'           => true,
          'cleanup'             => array($this, 'cleanupDataLot')
        ));
    } catch (Exception $e) {
      printf('%s%s', $e->getMessage(), PHP_EOL);
      return false;
    }
    
    $this->progress();
    $data = $this->lot_options;
    $data['parsed_at'] = date('Y-m-d H:i:s');
    $data['organization_link'] = $url;
    $data['currency'] = 'RUR';
    $data['exchange'] = 1;
    if (!isset($data['params'])) {
      $data['params'] = array();
    }

    preg_match('/<tr><td>Aдрес:\s*<\/td>\s*<td.[^<>]*?>(.+?)<\/td><\/tr>/is', $html, $matches);
    if (!empty($matches[1])) {
      $data['title'] = $matches[1];
    }
    
    $data['date'] = date('Y-m-d H:i:s', strtotime('-'.mt_rand(0, 23).' minute'));

    preg_match('/<td.[^<>]*?>Цена:\s*<\/td>\s*<td.[^<>]*?>(.[^<>]+?)<\/td>/is', $html, $matches);
    if (!empty($matches[1])) {
      if (mb_stripos($matches[1], 'тыс', null, 'utf-8') !== false) {
        $data['price'] = intval(preg_replace('/\D+/', '', $matches[1]))*10;
      } else {
        $data['price'] = mb_substr($matches[1], 0, mb_stripos($matches[1], '.', null, 'utf-8'), 'utf-8');
        $data['price'] = intval(preg_replace('/\D+/', '', $data['price']));
      }
    }

    if (empty($data['price']) || !($data['price'] > 0)) {
      ParseLogger::writeError($url, ParseLogger::EMPTY_PRICE);
      return false;
    } elseif ($this->lot_options['type'] == 'commercial-rent') {
      $data['params']['Арендная ставка кв.м/год'] = $data['price'];
    }

    preg_match('/<h3>Дополнительная информация<\/h3>\s*<p>(.+?)<\/p>/is', $html, $matches);
    if (!empty($matches[1])) {
      $data['description'] = $matches[1];
    }
    
    preg_match('/var\slat\s+\=\s*([\d\.]+);.+?var\slng\s+\=\s*([\d\.]+);/is', $html, $matches);
    if (!empty($matches[1]) && !empty($matches[2])) {
      $data['latitude']  = mb_substr($matches[1], 0, 10);
      $data['longitude'] = mb_substr($matches[2], 0, 10);
    }

    preg_match('/<td.[^<>]*>(?:Контакт|Телефон):\s*<\/td>\s*<td.[^<>]*>(.+?)(?:<br>)*?<\/td>/is', $html, $matches);
    if (!empty($matches[1])) {
      $data['organization_contact_phone'] = preg_replace('/^\s*|\s*$/', '', $matches[1]);
    }
    if (empty($data['organization_contact_phone'])) {
      ParseLogger::writeError($url, ParseLogger::EMPTY_PHONE);
      return false;
    }

    preg_match('/<td.[^<>]*>Агент:\s*<\/td>\s*<td.[^<>]*>(.+?)<\/td>/is', $html, $matches);
    if (!empty($matches[1])) {
      $data['organization_contact_name'] = $matches[1];
    } else {
      preg_match('/<td.[^<>]*>Агентство:\s*<\/td>\s*<td.[^<>]*>(.+?)<\/td>/is', $html, $matches);
      if (!empty($matches[1])) {
        if (mb_stripos($matches[1], ',', null, 'utf-8') !== false) {
          $agency = mb_substr($matches[1], 0, mb_stripos($matches[1], ',', null, 'utf-8'), 'utf-8');
        } else {
          $agency = $matches[1];
        }
        if (mb_stripos($agency, '"', null, 'utf-8') !== false || mb_stripos($agency, '«', null, 'utf-8') !== false) {
          $data['organization_contact_name'] = 'Агентство '.$agency;
        } else {
          $data['organization_contact_name'] = 'Агентство "'.$agency.'"';
        }
      }
    }

    preg_match_all('/<td.*?[^<>]*>(.+?):\s*<\/td>\s*<td.[^<>]*>(.+?)<\/td>/is', $html, $matches);
    if (!empty($matches[1])) {
      foreach ($matches[1] as $i => $param_name) {
        $data['params'][$param_name] = trim($matches[2][$i]);
      }
    }
    
    ParseTools::preg_clear_cache();
    $this->progress();
    $data = $this->parseLotData($data, $n);

    return $data;
  }

  /**
   * Fix lot data
   * @param array $data
   * @return array
   */
  private function parseLotData(array $data, $n) {
    $parsed = array('photos' => array());

    foreach ($data as $key => $value) {
      $this->progress();
      switch ($key) {
        case 'title':
          $parsed = array_merge($parsed, $this->parseAddress($data, $value, $n));
          break;

        case 'description':
          $value = preg_replace('/(\s*<(br|p).*>\s*)+/isU', "\n", $value);
          $value = preg_replace('/(<a.+<\/a>)|(<.+>)/isU', ' ', $value);
          $value = preg_replace('/http:\/\/[^\s]+/is', '', $value);
          $value = preg_replace('/w{0,3}\.*[a-z0-9-\.]+\.[a-z\.]{2,6}/isu', '', $value);
          $value = preg_replace('/((e-*mail|е-*м[ае][ий]л)(:|\.|\s*-))*\s*([a-z0-9_\.-]+)@([a-z0-9_\.-]+)\.([a-z\.]{2,6})\.*/isu', '', $value);
          $value = preg_replace('/ +/', ' ', $value);
          $value = preg_replace('/^\s+|\s+$/m', '', $value);
          $parsed['description'] = mb_substr($value, 0, 1500, 'utf-8');
          break;

        case 'date':
          $parsed['created_at'] = date('Y-m-d H:i:s', strtotime($value));
          $parsed['active_till'] = date('Y-m-d H:i:s', strtotime('+'.ParseTools::getLifetime($this->lot_options).' days'));
          if (strtotime($parsed['active_till']) < time()) {
            $parsed['status'] = 'inactive';
          } else {
            $parsed['status'] = 'active';
          }
          break;

        case 'photos':
          foreach ($value as $i => $item) {
            $parsed['photos'][] = $item;
          }
          break;

        case 'organization_contact_phone':
          $value = preg_replace('/\s+/', ' ', $value);
          if (preg_match('/^\d+\s*\(\d{3}\).+/i', $value)) {
            $value = preg_replace('/^\d/', '+7', $value);
          }
          $parsed['organization_contact_phone'] = $value;
          break;

        case 'params':
          $value = $this->parseLotParams($value);
          if ($this->lot_options['type'] == 'commercial-rent') {
            $value = $this->calcRentRate($value);
          }
          $value = $this->fixLotParams($value);
          $parsed['params'] = $value;
          break;

        default:
          $parsed[$key] = $value;
          break;
      }
    }

    return $parsed;
  }

  /**
   * Fix lot additional params
   * @param array $params
   * @return array $params
   */
  private function parseLotParams(array $params) {
    $parsed_keys = array();
    foreach ($params as $key => &$value) {
      $this->progress();
      if (in_array($key, array('Aдрес',
                              'Район/Местность',
                              'Регион',
                              'Метро',
                              'Направление',
                              'Тип',
                              'Цена',
                              'Агентство',
                              'Контакт',
                              'Агент',
                              'Подробнее',
                              'Куда выходят окна',
                              'Статус квартиры'))) {
        unset($params[$key]);
        continue;
      }
      if (in_array($key, $parsed_keys)) continue;

      if ($key == 'Количество комнат' && mb_strpos($this->lot_options['type'], 'house') === false) {
        unset($params[$key]);
        $value = $value.ending($value, '', '-х', '-ти', '-ми').' комнатная квартира';
        $params = array_merge($params, array('Тип предложения' => $value));
        $parsed_keys[] = 'Тип предложения';
        continue;
      }

      if ($key == 'Тип дома' && mb_strpos($this->lot_options['type'], 'house') === false) {
        unset($params[$key]);
        $params = array_merge($params, array('Тип здания' => $value));
        $parsed_keys[] = 'Тип здания';
      }

      if ($key == 'Туалет') {
        unset($params[$key]);
        switch ($value) {
          case 'Р':
            $val = 'раздельный';
            break;
          case 'С':
          case 'Смежный':
            $val = 'совмещенный';
            break;
          case '2':
          case '3':
          case '4':
            $val = $value.' с/у';
            break;
          default:
            continue 2;
        }
        
        $params = array_merge($params, array('Санузел' => $val));
        $parsed_keys[] = 'Санузел';
        continue;
      }
      if ($key == 'Наличие балкона' || $key == 'Балкон') {
        unset($params[$key]);
        if (in_array($value, array('+','да','есть'))) {
          $params = array_merge($params, array('Балкон/лоджия' => 'балкон'));
        }
        continue;
      }
      if ($key == 'Наличие телефона' || $key == 'Телефон') {
        unset($params[$key]);
        if (in_array($value, array('+','да','есть'))) {
          if (!empty($params['Детали'])) {
            $params['Детали'] .= ', телефон';
          } else {
            $params['Детали'] = 'телефон';
          }
        }
        continue;
      }
      if ($key == 'Наличие лифта' || $key == 'Лифт') {
        unset($params[$key]);
        if (in_array($value, array('+','да','есть'))) {
          if (!empty($params['Детали'])) {
            $params['Детали'] .= ', лифт';
          } else {
            $params['Детали'] = 'лифт';
          }
        }
        continue;
      }
      if ($key == 'Наличие мусоропровода' || $key == 'Мусоропровод') {
        unset($params[$key]);
        if (in_array($value, array('+','да','есть'))) {
          if (!empty($params['Детали'])) {
            $params['Детали'] .= ', мусоропровод';
          } else {
            $params['Детали'] = 'мусоропровод';
          }
        }
        continue;
      }
      if ($key == 'Наличие охраны' || $key == 'Охрана') {
        unset($params[$key]);
        if (in_array($value, array('+','да','есть'))) {
          if (!empty($params['Детали'])) {
            $params['Детали'] .= ', охрана';
          } else {
            $params['Детали'] = 'охрана';
          }
        }
        continue;
      }
      if ($key == 'Наличие парковки' || $key == 'Парковка') {
        unset($params[$key]);
        if (in_array($value, array('+','да','есть'))) {
          if (!empty($params['Детали'])) {
            $params['Детали'] .= ', парковка';
          } else {
            $params['Детали'] = 'парковка';
          }
        }
        continue;
      }

      if ($key == 'Вода') {
        unset($params[$key]);
        $params = array_merge($params, $this->parseLotParams(array('Водопровод' => $value)));
        continue;
      }

      if (mb_stripos($key, 'Состояние', null, 'utf-8') !== false) {
        unset($params[$key]);
        switch ($this->lot_options['type']) {
          case 'apartament-sale':
            $key = 'Ремонт';
            break;
          case 'apartament-rent':
            $key = 'Состояние/ремонт';
            break;
          case 'commercial-rent';
            $key = 'Состояние, отделка, готовность';
            break;
          case 'commercial-sale';
            $key = 'Состояние, отделка';
            break;
          case 'house-rent';
          case 'house-sale';
            $key = 'Ремонт/состояние';
            break;
        }
        
        $params = array_merge($params, $this->parseLotParams(array($key => $value)));
        $parsed_keys[] = $key;
        continue;
      }

      if ($key == 'Расстояние от МКАД') {
        unset($params[$key]);
        $params = array_merge($params, $this->parseLotParams(array('Удаленность от города' => $value)));
        continue;
      }

      if ($key == 'Этаж/Этажность') {
        unset($params[$key]);
        if (mb_strpos($value, '/', null, 'utf-8')) {
          $add = array_combine(
              explode('/', preg_replace('/\s+/is', '', $key)),
              explode('/', preg_replace('/\s+/is', '', $value))
            );
          $params = array_merge($params, $this->parseLotParams($add));
        }
        continue;
      }

      if ($key == 'Общая площадь') {
        unset($params[$key]);
        $key .= ' (<sup>';
      }
      if (mb_stripos($key, '<sup>', null, 'utf-8')) {
        unset($params[$key]);
        $key = mb_substr($key, 0, mb_stripos($key, '(', null, 'utf-8')-1, 'utf-8');
        $value = str_replace(',', '.', preg_replace('/\s+/is', '', $value));

        if ($key == 'Общая площадь') {
          switch ($this->lot_options['type']) {
            case 'commercial-sale':
            case 'commercial-rent':
              $key = 'Общая площадь помещения';
              break;
            case 'house-sale':
            case 'house-rent':
              $key = 'Площадь дома';
              break;
            default:
              $key = $key;
          }
        }
        $params = array_merge($params, array($key => $value));
      } elseif (is_string($value)) {
        $value = preg_replace('/<.+>/isU', '', $value);
      }

      if ($key == 'Площадь участка (сот)' || $key == 'Площадь участка') {
        unset($params[$key]);
        if (mb_stripos($this->lot_options['type'], 'commercial') !== false) {
          $key = 'Общая площадь земли';
        } else {
          $key = 'Площадь участка';
        }
        preg_match('/(\d+\.*\d*)/is', $value, $matches);
        if (!empty($matches[1])) {
          $value = $matches[1];
          if ($key == 'Общая площадь земли') {
            $value /= 100;
          }
          $params = array_merge($params, array($key => $value));
          $parsed_keys[] = $key;
          continue;
        }
      }

      if (in_array($key, array('Общая площадь',
                              'Площадь дома',
                              'Площадь кухни',
                              'Жилая площадь',
                              'Общая площадь помещения',
                              'Удаленность от города'))) {
        preg_match('/(\d+\.*\d*)/is', $value, $matches);
        if (!empty($matches[1])) {
          $value = $matches[1];
          $params = array_merge($params, array($key => $value));
        } else {
          unset($params[$key]);
        }
        $parsed_keys[] = $key;
        continue;
      }

      if (preg_match('/^[\d\s.,]+$/mi', $value)) {
        $value = str_replace(',', '.', preg_replace('/[^\d.,]+[^\d]/', '', $value));
      } elseif (is_string($value)) {
        $value = preg_replace('/^\s+|\s+$/', '', $value);
      }
      
      if (empty($value) || $value == '?') {
        unset($params[$key]);
      } elseif ($value == '+') {
        $value = 'есть';
      }
    }

    return $params;
  }

  /**
   * @param array $params
   * @return array
   */
  private function fixLotParams(array $params) {
    foreach ($params as $key => $value) {
      $query = new Doctrine_Query();
      $field = $query->select('f.field_id, f.type, f2.value, f2.type')
          ->from('FormItem f')
          ->leftJoin('f.FormField f2 WITH f.field_id = f2.id')
          ->where('f2.label LIKE ?', $key)
          ->fetchArray();

      $count = count($field);
      $i = 0;
      if ($count > 1) {
        for ($i; $i<$count; $i++) {
          if ($field[$i]['type'] == $this->lot_options['type'])
            break;
        }
      }

      unset($params[$key]);

      if (!empty($field[$i]['field_id'])) {
        $params[$field[$i]['field_id']] = $value;
      } else {
        continue;
      }

      if ($key == 'Детали' || $key == 'Доп. строения') continue;

      if (in_array($field[$i]['field_id'], array(1,7,8,9,26,27,36,46,47))) {
        $params[$field[$i]['field_id']] = round($value, 2);
      } elseif (in_array($field[$i]['field_id'], array(3,4,35))) {
        $params[$field[$i]['field_id']] = intval($value);
      } elseif ($field[$i]['field_id'] == 5) {
        $params[$field[$i]['field_id']] = ParseTools::getBuiltYear($value);
        if (empty($params[$field[$i]['field_id']])) {
          unset($params[$field[$i]['field_id']]);
          continue;
        }
      }

      if (isset($field[$i]['FormField']['type']) &&
          ($field[$i]['FormField']['type'] == 'select' || $field[$i]['FormField']['type'] == 'multiple')) {
        switch ($value) {
          case 'да':
          case 'нет':
            $params[$field[$i]['field_id']] = $value;
            break;
          case 'эксклюзивный евроремонт':
            $params[$field[$i]['field_id']] = 'евроремонт';
            break;
          case 'хорошее состояние':
            $params[$field[$i]['field_id']] = 'после косметического ремонта';
            break;
          case 'монолит.-блоч.':
            $params[$field[$i]['field_id']] = 'Блочный';
            break;
          case 'Кирпично-Монолитный':
            $params[$field[$i]['field_id']] = 'Монолитно-кирпичный';
            break;
          case 'Деревянный':
            $params[$field[$i]['field_id']] = 'Дерево';
            break;
          case 'элитный':
          case 'сталинский':
            unset($params[$field[$i]['field_id']]);
            break;
          default:
            $params = ParseTools::selectSimilar($field[$i]['FormField']['value'], $params, $field[$i]['field_id']);
        }
      }
      
      $field->free();
      unset($field);
    }
    
    return $params;
  }
  

  private function calcRentRate($params) {
    if ($params['Арендная ставка кв.м/год'] > 60000 && $params['Арендная ставка кв.м/год'] < 720000) {
      $params['Арендная ставка кв.м/год'] = ceil($params['Арендная ставка кв.м/год']/12);
    } elseif ($params['Арендная ставка кв.м/год'] > 720000) {
      if (!empty($params['Общая площадь помещения'])) {
        $params['Арендная ставка кв.м/год'] = (intval($params['Арендная ставка кв.м/год']) / floatval($params['Общая площадь помещения']))*12;
        $params['Арендная ставка кв.м/год'] = ceil($params['Арендная ставка кв.м/год']);
      }
    }
    return $params;
  }


  /**
   * Parse address row
   * @param string $value
   * @return array $address
   */
  private function parseAddress($data, $value, $n) {
    $city = ParseTools::getRegionName($this->lot_options['region_id']);

    $address1 = '';
    $address2 = '';
    $this->lots_data[$n]['dist'] = preg_replace('/\s*\([^)]+\)/isu', '', $this->lots_data[$n]['dist']);
    $value = preg_replace('/[^,]+р-н[^,]*,*/isu', '', $value);

    if (($pos_m = mb_stripos($this->lots_data[$n]['dist'], 'м.', null, 'utf-8')) !== false) {
      $metro = mb_substr($this->lots_data[$n]['dist'], 0, $pos_m-1, 'utf-8');

      if (($pos_c = mb_strripos($metro, ',', null, 'utf-8')) !== false) {
        list($value_add, $metro) = explode(', ', $metro);
        $value = $value_add.', '.$value;
      }

      $address1 = $city.', м. '.$metro;
    } else {
      if ($this->lot_options['region_id'] == 77) {
        $this->lot_options['region_id'] = 50;
        $city = 'Московская обл.';
      } elseif ($this->lot_options['region_id'] == 78) {
        $this->lot_options['region_id'] = 47;
        $city = 'Ленинградская обл.';
      } elseif ($this->lot_options['region_id'] == 52 
              && mb_stripos($this->lots_data[$n]['dist'], 'р-н', null, 'utf-8') !== false) {
        switch ($this->lots_data[$n]['dist']) {
          case 'Нижегородский р-н':
          case 'Приокский р-н':
          case 'Советский р-н':
          case 'Автозаводский р-н':
          case 'Канавинский р-н':
          case 'Ленинский р-н':
          case 'Московский р-н':
          case 'Сормовский р-н':
            $this->lots_data[$n]['dist'] = 'Нижний Новгород';
        }
      }
      $this->lots_data[$n]['dist'] = preg_replace('/[^,]+р-н[^,]*,*/isu', '', $this->lots_data[$n]['dist']);
      $this->lots_data[$n]['dist'] = preg_replace('/[^,]+напр\.[^,]*,*/isu', '', $this->lots_data[$n]['dist']);

      if ((mb_stripos($this->lot_options['type'], 'house') !== false || mb_stripos($this->lot_options['type'], 'commercial') !== false)
              && $this->lot_options['region_id'] == 50) {
        if (mb_stripos($this->lots_data[$n]['dist'], ', ', null, 'utf-8') !== false) {
          if (mb_stripos($this->lot_options['type'], 'house') !== false) {
            list($value, $address1) = explode(', ', $this->lots_data[$n]['dist'], 2);
          } else {
            list($address1, $value) = explode(', ', $this->lots_data[$n]['dist'], 2);
          }
        } else {
          preg_match('/^([^,\.]+\s[а-яА-Я]{1,3}\.)\s(.+)$/isu', $this->lots_data[$n]['dist'], $matches);
          if (!empty($matches[1]) && !empty($matches[2])) {
            if (mb_stripos($this->lot_options['type'], 'house') !== false) {
              $address1 = $matches[2];
              $value = $matches[1];
            } else {
              $address1 = $matches[1];
              $value = $matches[2];
            }
          } elseif (mb_stripos($value, ', ', null, 'utf-8') !== false) {
            list($address1, $value) = explode(', ', $value, 2);
          } else {
            $address1 = $value;
            $value = '';
          }
        }
      } elseif (!empty($this->lots_data[$n]['dist'])) {
        $address1 = preg_replace('/\s.{1}\.*$/isu', '', $this->lots_data[$n]['dist']);
      } elseif (mb_stripos($value, ', ', null, 'utf-8') !== false) {
        list($address1, $value) = explode(', ', $value, 2);
      } else {
        $address1 = $value;
        $value = '';
      }

      if (preg_match('/[а-яА-Я0-9-]{3,}(\s+[а-яА-Я]{1,3}\.*$)/isu', $address1)) {
        $address1 = preg_replace('/\s+[а-яА-Я]{1,3}\.*$/isu', '', $address1);
      }
      if (!empty($address1)) {
        $address1 = $city.', '.$address1;
      }
    }
    
    $address2 = $value;
    $address2 = str_replace('№', '', $address2);
    $address2 = preg_replace('/^[^,]+(район|р-н|обл\.)(,\s*|$)/isu', '', $address2);
    $city = preg_replace('/\s+/iu', '[^,]*', $city);
    $address2 = preg_replace('/^'.$city.'(\s|,|$)+|[^,]+'.$city.'(\s|,|$)+/isu', '', $address2);
    $address2 = preg_replace('/,\s*\d+[^,]+ от [^,]+/isu', '', $address2);
    $address2 = preg_replace('/[,\s]*рядом с [^,]+/isu', '', $address2);
    $address2 = preg_replace('/,* (д|дом)(\.|\s)/isu', ', ', $address2);
    $address2 = preg_replace('/ул\.* (д|дом)(\.|\s)/isu', 'ул., ', $address2);
    $address2 = preg_replace('/,[^,](\d+),*\s(корпус\s|корп(?:\.*|\s+)|кор(?:\.*|\s+)|стр(?:\.*|\s+)|строение\s)+\s*(\d+)/isu', ', $1 $2 $3', $address2);
    
    $data = array(
      'address1' => $address1,
      'address2' => $address2
    );
    
    return array_merge($data, array('address_info' => null));
  }


  /**
   * Fetch lot urls
   * @return void
   */
  private function getLocations() {
    while (count($this->lots) < $this->limit) {
      $left = array_diff($this->pages, $this->pages_fetched);
      if (empty($left)) {
        break;
      }
      $url = array_shift($left);

      $this->pages_fetched[] = $url;
      $this->progress(sprintf('Fetching page %d/%d. Found %d (max %d) lots',
          count($this->pages_fetched),
          count($this->pages),
          count($this->lots),
          $this->limit));

      try {
        $data = $this->fetch($url, array(
            'strip_comments'      => true,
            'strip_html'          => true,
            'only_body'           => true,
            'cleanup'             => array($this, 'cleanupDataList')
          ));
      } catch (Exception $e) {
        printf('%s%s', $e->getMessage(), PHP_EOL);
        continue;
      }

      if (!$this->extractLinks($data)) break;
    }
  }

  /**
   * Extract lot and page links
   * @param string $html
   * @return void|false
   */
  private function extractLinks($html) {
    // first find lot links
    if ($this->lot_options['type'] == 'apartament-sale' || $this->lot_options['type'] == 'apartament-rent') {
      $suffix = 'flats';
      if ($this->lot_options['region_id'] != 77) {
        $regex_links = '/<td\sclass="lef">\s*<\/td>\s*<td>([^<>]+?)<\/td>.+?href="([^<>]+?detail.+?)">(.+?)\s*<\/a>/is';
        $suffix = 'regions/'.$suffix;
      } else {
        $regex_links = '/<td>(.[^<>]+)<\/td>\s*<td\stitle.[^<>]+?>\s*<a\shref=[\'"](.[^<>]+?detail_.+?)[\'"]>/is';
        $suffix = 'msk/'.$suffix;
      }

      preg_match_all($regex_links, $html, $matches);
      foreach ($matches[2] as $i => $link) {
        $link = 'http://www.bn.ru'.$link;
        $this->progress();
        if (!in_array($link, $this->lots)) {
          $query = new Doctrine_Query();
          $query->from('Lot')->where('organization_link = ?', $link);
          if (count($this->lots) == $this->limit) {
            return false;
          } else {
            $n = array_push($this->lots, $link);
            $this->lots_data[$n-1] = array('dist' => $matches[1][$i]);
          }
        }
      }
    } elseif ($this->lot_options['type'] == 'house-sale' || $this->lot_options['type'] == 'house-rent') {
      $suffix = 'country';
      if ($this->lot_options['region_id'] != 77) {
        $regex_links = '/<td\sclass="lef">\s*<\/td>\s*<td>([^<>]+?)<\/td>.+?href="([^<>]+?detail.+?)">(.+?)\s*<\/a>/is';
        $suffix = 'regions/'.$suffix;
        $link_key = 2;
        $dist_key = 1;
      } else {
        $regex_links = '/<td\stitle=[^<>]+><a\shref=[\'"]([^<>]+)[\'"]>\s*([^\/]+)\s*<\/a><\/td>\s*<td>\d+\.*\d*<\/td>[^"]+<td>(.*?)<\/td>\s*<\/tr>/is';
        $suffix = 'msk/'.$suffix;
        $link_key = 1;
        $dist_key = 2;
      }

      preg_match_all($regex_links, $html, $matches);
      foreach ($matches[$link_key] as $i => $link) {
        if (empty($matches[3][$i])) continue;
        
        $link = 'http://www.bn.ru'.$link;
        $this->progress();
        if (!in_array($link, $this->lots)) {
          $query = new Doctrine_Query();
          $query->from('Lot')->where('organization_link = ?', $link);
          if (count($this->lots) == $this->limit) {
            return false;
          } else {
            $n = array_push($this->lots, $link);
            $dist = preg_replace('/\s+/', ' ', $matches[$dist_key][$i]);
            $dist = preg_replace('/\s*<br>\s*/i', ', ', $dist);
            $dist = preg_replace('/(\s*|,\s*)$/i', '', $dist);
            $this->lots_data[$n-1] = array('dist' => $dist);
          }
        }
      }
    } elseif ($this->lot_options['type'] == 'commercial-sale' || $this->lot_options['type'] == 'commercial-rent') {
      $suffix = 'commerce';
      if ($this->lot_options['region_id'] != 77) {
        $regex_links = '/<td\sclass="lef">\s*<\/td>\s*<td>([^<>]+?)<\/td>.+?href="([^<>]+?detail.+?)">(.+?)\s*<\/a>/is';
        $suffix = 'regions/'.$suffix;
        $link_key = 2;
        $dist_key = 1;
      } else {
        $regex_links = '/<td\stitle=[^<>]+>\s*<a\shref=[\'"]([^<>]+)[\'"]>\s*([^\/]+)\s*<\/a><\/td>[^"]+<td>([^"]*?)<\/td>/is';
        $suffix = 'msk/'.$suffix;
        $link_key = 1;
        $dist_key = 2;
      }

      preg_match_all($regex_links, $html, $matches);
      foreach ($matches[$link_key] as $i => $link) {
        if (empty($matches[3][$i])) continue;

        $link = 'http://www.bn.ru'.$link;
        $this->progress();
        if (!in_array($link, $this->lots)) {
          $query = new Doctrine_Query();
          $query->from('Lot')->where('organization_link = ?', $link);
          if (count($this->lots) == $this->limit) {
            return false;
          } else {
            $n = array_push($this->lots, $link);
            $dist = preg_replace('/\s+/', ' ', $matches[$dist_key][$i]);
            $dist = preg_replace('/\s*<br>\s*/i', ', ', $dist);
            $dist = preg_replace('/\s\(.+\)/is', '', $dist);
            $dist = preg_replace('/(\s*|,\s*)$/i', '', $dist);
            $this->lots_data[$n-1] = array('dist' => $dist);
          }
        }
      }
    }

    // ... after extract page locations
    preg_match_all('/<a[^<>]*?href="([^<>]+?)"[^<>]*?>\d+<\/a>/is', $html, $matches);
    foreach ($matches[1] as $link) {
      $link = 'http://www.bn.ru/'.$suffix.'/search/'.$link;
      $this->progress();
      if ($link) {
        if (!in_array($link, $this->pages)) {
          $this->pages[] = $link;
        }
      }
    }
    
    return true;
  }

  /**
   * Cleanup list
   * @param string $html
   * @return string|null
   */
  protected function cleanupDataList($html) {
    preg_match('/<form.+?id=.*?>.+?<p\sclass="right\spage">(.+?)<\/form>/ims', $html, $matches);
    if (empty($matches[1])) {
      return null;
    }
    
    return $matches[1];
  }

  /**
   * Cleanup data for lot
   * @param string $html
   * @return string|null
   */
  protected function cleanupDataLot($html) {
    preg_match('/<div\sclass="object_data">(.+?)<div\sclass="object_data2">(.+?)<div\sclass="clear">/ims', $html, $matches);
    if (empty($matches[1])) {
      return null;
    }
    if (!empty($matches[2])) {
      return $matches[1].$matches[2];
    }

    return $matches[1];
  }
}