<?php

require dirname(__FILE__).'/../helper/WordHelper.php';

/**
 * Class for fetching http://eip.ru/ lots
 *
 * @author Grechko Aleksey
 */
class Fetcher_Eip extends Fetcher
{
  public
    $lots_parsed = 0,
    $lots_fetched = 0;
  private
    $limit = null,
    $pages = array(),
    $pages_fetched = array(),
    $lots = array(),
    $lot_options = array(),
    $lots_prolong = array(),

    $exists_exceptions = 0,
    $exists_exceptions_limit = 100;

  /**
   * Constructor
   * @param array $options
   * @param callable $progress_callable = null
   * @return Fetcher_Eip
   */
  public function __construct($options) {
    $this->limit = $options['limit'];
    $this->pages[] = $options['url'];
    $this->lot_options = $options['data'];

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
 var_dump($data); continue;
          $check_query = new Doctrine_Query();
          $check_query->from('Lot')->where('organization_link = ?', $data['organization_link']);
          if ($check_query->count()) {
            ParseLogger::writeError($url, ParseLogger::EXISTS);
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
          }

          if (count($photos)) {
            $images = array();
            $source = sprintf('%s/%s/source', sfConfig::get('sf_web_dir'), sfConfig::get('app_upload_tmp_dir'));

            $cntr = 0;
            foreach ($photos as $i => $image) {
              if ($cntr > 7) break;

              $this->progress();
              $filename = $lot->id.'_'.($i+1);
              $raw = @file_get_contents($image, 0, ParseTools::getStreamContext());
              $image = "$source/$filename";
              if ($raw && file_put_contents($image, $raw)) {
                chmod($image, 0666);

                $ext = null;
                $info = getimagesize($image);
                if ($info['mime'] == 'image/gif') {
                  $ext = '.gif';
                }
                elseif ($info['mime'] == 'image/jpeg') {
                  $ext = '.jpg';
                }
                elseif ($info['mime'] == 'image/png') {
                  $ext = '.png';
                }

                if (!$ext) {
                  unlink($image);
                  continue;
                }

                $filename = ($i+1).$ext;
                if (rename($image, $lot->full_image_path . '/' . $filename)) {
                  chmod($lot->full_image_path .'/' . $filename, 0666);
                  $images[] = $filename;
                  $cntr++;
                }
              }
            }
            if (count($images)) {
              $lot->images = $images;
              $lot->thumb = 1;
            }
          }

          $lot->save();

          $this->lots_parsed++;
        }
        catch (Exception $e) {
          ParseLogger::writeError($url, ParseLogger::EXCEPTION, $e->getMessage());
          if ($need_delete) {
            try {
              $lot->delete();
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
          'strip_comments'        => true,
          'strip_html'            => true,
          'only_body'             => true,
          'cleanup'               => array($this, 'cleanupDataLot'),
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

    if ($this->lot_options['type'] == 'commercial-sale' || $this->lot_options['type'] == 'commercial-rent') {
      preg_match('/<tr><td>Операция, тип недвижимости<\/td><td[^>]*>([^<]+)<\/td><\/tr>/i', $html, $matches);
      if (empty($matches[1])) {
        ParseLogger::writeError($url, ParseLogger::EMPTY_PARAMS);
        return false;
      }
      $data['params']['Тип недвижимости'] = ParseTools::matchCommercialType($matches[1]);
    }

    preg_match('/Объявление № \d+ [^\d<]+ ([^<]+)<\/th>/is', $html, $matches);
    if (!empty($matches[1])) {
      $data['date'] = date('Y-m-d H:i:s', strtotime($matches[1]));
    } else {
      $data['date'] = date('Y-m-d H:i:s', strtotime('-'.mt_rand(0, 723).' minute'));
    }

    preg_match('/Стоимость[^<]*<\/td><td>([^<]+)<\/td>/is', $html, $matches);
    if (!empty($matches[1])) {
      $data['price'] = intval(preg_replace('/\D/', '', $matches[1]));

      if (mb_strpos($matches[1], ' р/с') !== false) {
        $data['price'] *= 30;
      }
    }
    if (empty($data['price']) || !($data['price'] > 0)) {
      ParseLogger::writeError($url, ParseLogger::EMPTY_PRICE);
      return false;
    } elseif ($this->lot_options['type'] == 'commercial-rent') {
      $data['params']['Арендная ставка кв.м/год'] = $data['price'];
    }

    preg_match('/<td>Адрес<\/td><td[^>]*>([^<]+)/is', $html, $matches);
    if (!empty($matches[1])) {
      $data['title']['street'] = trim($matches[1]);
    }
    preg_match('/<td>Метро<\/td><td[^>]*>([^<]+)/is', $html, $matches);
    if (!empty($matches[1])) {
      $data['title']['metro'] = trim($matches[1]);
    }
    preg_match('/<td>Район<\/td><td[^>]*>([^<]+)/is', $html, $matches);
    if (!empty($matches[1])) {
      $data['title']['raion'] = trim($matches[1]);
    }
    if (empty($data['title']['street'])) {
      ParseLogger::writeError($url, ParseLogger::EMPTY_ADDRESS);
      return false;
    }

    preg_match('/<td>Контактная информация<\/td><td[^>]*>(.+?)<\/td><\/tr>/is', $html, $matches);
    if (!empty($matches[1])) {
      $contacts = explode('<br />', $matches[1]);
      foreach ($contacts as $contact) {
        if (preg_match('/mailto:/i', $contact)) continue;

        preg_match('/<a href="\/agency\/[^>]+>([^<]+)<\/a>/is', $contact, $matches);
        if (!empty($matches[1])) {
          if (empty($data['organization_contact_name'])) {
            $data['organization_contact_name'] = 'Агентство '.$matches[1];
          } else {
            $data['organization_contact_name'] .= ', Агентство '.$matches[1];
          }
          continue;
        }

        if (preg_match('/\d{3,}/is', $contact)) {
          if (empty($data['organization_contact_phone'])) {
            $data['organization_contact_phone'] = trim($contact);
          } else {
            $data['organization_contact_phone'] .= ', '.trim($contact);
          }
          continue;
        }

        if (empty($data['organization_contact_name'])) {
          $data['organization_contact_name'] = preg_replace('/<[^>]+>|&[^;]+;/is', '', $contact);
        } else {
          $data['organization_contact_name'] .= ', '.preg_replace('/<[^>]+>|&[^;]+;/is', '', $contact);
        }
      }
    }

    preg_match('/<td>Дополнительная информация<\/td><td[^>]*>(.+?)<\/td><\/tr>/is', $html, $matches);
    if (!empty($matches[1])) {
      $data['description'] = $matches[1];
    }
    if (empty($data['organization_contact_phone']) && !empty($data['description'])) {
      preg_match('/(?:Тел(?:ефон|\.)*|Т\.):*\s*([0-9-+()]+[0-9-+(), ]*)/isu', $data['description'], $matches);
      if (!empty($matches[1])) {
        $data['organization_contact_phone'] = $matches[1];
      }
    }
    if (empty($data['organization_contact_phone'])) {
      ParseLogger::writeError($url, ParseLogger::EMPTY_PHONE);
      return false;
    }

    preg_match_all('/href="([^"]+\/photos.php[^"]+)"/is', $html, $matches);
    if (!empty($matches[1])) {
      $data['photos'] = $matches[1];
    }
    
    preg_match_all('/<tr><td>(.+?)<\/td><td[^>]*>(.*?)<\/td><\/tr>/is', $html, $matches);
    if (!empty($matches[1])) {
      foreach ($matches[1] as $i => $param_name) {
        $data['params'][$param_name] = $matches[2][$i];
      }
    } else {
      ParseLogger::writeError($url, ParseLogger::EMPTY_PARAMS);
      return false;
    }

    ParseTools::preg_clear_cache();
    $this->progress();
    $data = $this->parseLotData($data);

    return $data;
  }

  /**
   * Fix lot data
   * @param array $data
   * @return array
   */
  private function parseLotData(array $data) {
    $parsed = array('photos' => array());

    foreach ($data as $key => $value) {
      $this->progress();
      switch ($key) {
        case 'title':
          $parsed = array_merge($parsed, $this->parseAddress($data, $value));
          break;

        case 'description':
          $value = preg_replace('/(\s*<(br|p).*>\s*)+/isU', ' ', $value);
          $value = preg_replace('/(<a.+<\/a>)|(<.+>)/isU', ' ', $value);
          $value = preg_replace('/<.+>/iU', '', $value);
          $value = preg_replace('/http:\/\/[^\s]+/is', '', $value);
          $value = preg_replace('/w{0,3}\.*[a-z0-9-\.]+\.[a-z\.]{2,6}/isu', '', $value);
          $value = preg_replace('/((e-*mail|е-*м[ае][ий]л)(:|\.|\s*-))*\s*([a-z0-9_\.-]+)@([a-z0-9_\.-]+)\.([a-z\.]{2,6})\.*/isu', '', $value);
          $value = preg_replace('/\s+/', ' ', $value);
          $value = preg_replace('/\s+,/', ',', $value);
          $value = preg_replace('/^\s+|\s+$/m', '', $value);
          $parsed['description'] = mb_substr($value, 0, 1500, 'utf-8');
          break;

        case 'date':
          $parsed['created_at'] = $value;
          $parsed['active_till'] = date('Y-m-d H:i:s', strtotime('+'.ParseTools::getLifetime($this->lot_options).' days'));
          if (strtotime($parsed['active_till']) < time()) {
            $parsed['status'] = 'inactive';
          } else {
            $parsed['status'] = 'active';
          }
          break;

        case 'photos':
          foreach ($value as $i => $item) {
            $parsed['photos'][] = 'http://www.eip.ru'.$item;
          }
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
    $checked = array();
    foreach ($params as $key => &$value) {
      $this->progress();
      if (in_array($key, array('Операция, тип недвижимости',
                              'Район',
                              'Адрес',
                              'Метро',
                              'Дополнительная информация',
                              'Контактная информация',
                              'Вход',
                              'Подъезд',
                              'Канализация',
                              'Коммуникации',
                              'Горячая вода',
                              'Условие аренды',
                              'Статус земельного участка',
                              'Срок, мес.',
                              ))) {
        continue;
      }

      if (preg_match('/<a[^>]+href/', $key)) continue;

      if ($key == 'Этаж/этажность') {
        $etazhi = explode('/', $value, 2);
        if (count($etazhi) > 1) {
          if ($etazh = preg_replace('/\D/', '', $etazhi[0])) {
            $checked['Этаж'] = $etazh;
          }
          $key = 'Этажность';
          $value = preg_replace('/\D/', '', $etazhi[1]);
        } else {
          $key = 'Этаж';
          $value = preg_replace('/\D/', '', $value);
        }
      }

      if ($key == 'Тип дома' && mb_strpos($this->lot_options['type'], 'house') === false) {
        $key = 'Тип здания';
      }

      if ($key == 'Комнат в квартире') {
        $value = preg_replace('/\D/', '', $value);
        if (empty($params['Тип предложения']) && mb_strpos($this->lot_options['type'], 'apartament') !== false) {
          $key = 'Тип предложения';
          $value = $value.ending($value, '', '-х', '-ти', '-ми').' комнатная квартира';
        } elseif (mb_strpos($this->lot_options['type'], 'house') !== false) {
          $key = 'Количество комнат';
        }
      }

      if (mb_strpos($key, 'Площадь:') !== false) {
        $areas_names = explode('<br />', $key);
        $areas_values = explode('<br />', $value);

        foreach ($areas_names as $key => $val) {
          $value = preg_replace('/<sup>.+<\/sup>/', '', $areas_values[$key]);
          $value = preg_replace('/[^\d.]/', '', $value);
          if (empty($value)) continue;

          if ($val == ' - Общая') {
            switch ($this->lot_options['type']) {
              case 'apartament-sale':
              case 'apartament-rent':
                $area_type = 'Общая площадь';
                break;
              case 'commercial-sale':
              case 'commercial-rent':
                $area_type = 'Общая площадь помещения';
                break;
              case 'house-sale':
              case 'house-rent':
                $area_type = 'Площадь дома';
                break;
              default:
                continue;
            }
            
            $checked[$area_type] = floatval($value);

          } elseif ($val == ' - Жилая') {
            $checked['Жилая площадь'] = $value;
          } elseif ($val == ' - Кухни') {
            $checked['Площадь кухни'] = $value;
          } elseif ($val == ' - Участка') {
            if (mb_strpos($this->lot_options['type'], 'commercial') !== false) {
              $area_type = 'Общая площадь земли';
            } else {
              $area_type = 'Площадь участка';
            }
            
            $checked[$area_type] = $value;
          }
        }
        continue;
      }

      if ($key == 'Состояние') {
        switch ($this->lot_options['type']) {
          case 'apartament-sale':
            $key = 'Состояние помещения';
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
          default:
            continue;
        }

        $value = mb_strtolower($value, 'utf-8');
      }

      if ($key == 'Санузел') {
        $value = mb_strtolower($value, 'utf-8');
        switch ($value) {
          case 'совмещенный':
          case 'раздельный':
            break;
         case '2':
         case '3':
         case '4':
         case '5':
           $value .= ' с/у';
           break;
         default:
           continue;
        }
      }

      if ($key == 'Балкон') {
        $key = 'Балкон/лоджия';
        $value = mb_strtolower($value, 'utf-8');
        if (in_array($value, array('+','да','есть'))) {
          $value = 'балкон';
        }
      }
      if ($key == 'Мебель') {
        $key = 'Мебель';
        $value = mb_strtolower($value, 'utf-8');
        if (in_array($value, array('+','да','есть'))) {
          $value = 'да';
        }
      }
      if ($key == 'Холодильник' || $key == 'Стиральная машина') {
        $key = 'Оборудование/бытовая техника';
        $value = mb_strtolower($value, 'utf-8');
        if (in_array($value, array('+','да','есть'))) {
          $value = 'да';
        }
      }

      if (in_array($key, array('Электричество','Водопровод'))) {
        $value = mb_strtolower($value, 'utf-8');
        if (in_array($value, array('+','да','есть'))) {
          $value = 'есть';
        }
      }

      if (in_array($key, array('Гараж','Баня'))) {
        $key = mb_strtolower($key, 'utf-8');

        $value = mb_strtolower($value, 'utf-8');
        if (in_array($value, array('+','да','есть'))) {
          if (!empty($checked['Доп. строения'])) {
            $checked['Доп. строения'] .= ', '.$key;
          } else {
            $checked['Доп. строения'] = $key;
          }
        }
        continue;
      }

      if (in_array($key, array('Мусоропровод',
                               'Лифт',
                               'Телефон',
                               'Интернет',
                               'Парковка',
                               'Лес',
                               'Водоем',
                               'Охрана'
                              ))) {
        $key = mb_strtolower($key, 'utf-8');

        $value = mb_strtolower($value, 'utf-8');
        if (in_array($value, array('+','да','есть'))) {
          if (!empty($checked['Детали'])) {
            $checked['Детали'] .= ', '.$key;
          } else {
            $checked['Детали'] = $key;
          }
        }
        continue;
      }

      if (preg_match('/^[\d\s.,]+$/mi', $value)) {
        $value = str_replace(',', '.', preg_replace('/[^\d.,]+[^\d]/', '', $value));
      } elseif (is_string($value)) {
        $value = preg_replace('/^\s+|\s+$/', '', $value);
      }

      if (empty($value) || in_array($value, array('-','','?'))) {
        unset($params[$key]);
        continue;
      }

      $checked[$key] = $value;
    }

    return $checked;
  }

  /**
   * @param array $params
   * @return array
   */
  private function fixLotParams(array $params) { var_dump($params);
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
          case 'есть':
            $params[$field[$i]['field_id']] = $value;
            break;
          case 'Сталинка':
          case 'Сталинский':
          case 'Удовлетворительное':
          case 'без отделки':
          case 'Ст.ф.':
          case 'Бреж.':
            unset($params[$field[$i]['field_id']]);
            break;
          case 'Кирп.':
            $params[$field[$i]['field_id']] = 'Кирпичный';
            break;
          case 'Кирпично-монолитный':
            $params[$field[$i]['field_id']] = 'Монолитно-кирпичный';
            break;
          case 'Дер.':
          case 'Деревянный':
          case 'Брус':
            $params[$field[$i]['field_id']] = 'Дерево';
            break;
          case 'монолит':
            $params[$field[$i]['field_id']] = 'Монолит';
            break;
          case 'Хорошее':
            $params[$field[$i]['field_id']] = 'после косметического ремонта';
            break;
          case 'Отличное':
            $params[$field[$i]['field_id']] = 'в отличном состоянии';
            break;
          case 'Евро ремонт':
            $params[$field[$i]['field_id']] = 'евроремонт';
            break;
          default:
            $params = ParseTools::selectSimilar($field[$i]['FormField']['value'], $params, $field[$i]['field_id']);
        }
      }
    }

    return $params;
  }


  private function calcRentRate($params) {
    $params['Арендная ставка кв.м/год'] *= 12;
    return $params;
  }

  /**
   * Parse address row
   * @param string $value
   * @return array $address
   */
  private function parseAddress($data, $value) {
    $piter_raions = array(
        'Адмиралтейский', 'Василеостровский', 'Выборгский',
        'Калининский', 'Кировский', 'Колпинский',
        'Красногвардейский', 'Красносельский', 'Кронштадтский',
        'Курортный', 'Московский', 'Невский',
        'Петроградский', 'Петродворцовый', 'Приморский',
        'Пушкинский', 'Фрунзенский', 'Центральный',
    );
    $moscow_raions = array(
        'Центральный', 'Северный', 'Северо-Восточный',
        'Восточный', 'Юго-Восточный', 'Южный',
        'Юго-Западный', 'Западный', 'Северо-Западный',
        'Зеленоградский',
    );

    if ($this->lot_options['region_id'] == 77 && empty($value['metro']) &&
            !in_array($value['raion'], $moscow_raions)) {
      $this->lot_options['region_id'] = 50;
    }
    elseif ($this->lot_options['region_id'] == 78 && empty($value['metro']) &&
            !in_array($value['raion'], $piter_raions)) {
      $this->lot_options['region_id'] = 47;
    }

    $city = ParseTools::getRegionName($this->lot_options['region_id']);

    $address1 = $city;
    $address2 = '';

    if (preg_match('/зем\.* уч/is', $value['street'])) {
      $value['street'] = preg_replace('/[^,]*дом с зем[^,]* уч[^,]*,*\s*/isu', '', $value['street']);
    }

    if (!empty($value['metro'])) {
      $value['metro'] = preg_replace('/\s+\d*\s*мин.+$/isu', '', $value['metro']);
      $address1 .= ', м. '.$value['metro'];
    }

    if (preg_match('/\D\/\D/', $value['street'])) {
      $value['street'] = trim(mb_substr($value['street'], 0, mb_strpos($value['street'], '/', null, 'utf-8'), 'utf-8'));
    }
    $address2 = $value['street'];

    $data = array(
      'address1' => $address1,
      'address2' => $address2,
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
            'strip_comments'        => true,
            'strip_html'            => true,
            'cleanup'               => array($this, 'cleanupDataList'),
          ));
      } catch (Exception $e) {
        printf('%s%s', $e->getMessage(), PHP_EOL);
        continue;
      }

      if (!$this->extractLinks($data)) break;
    }

    if (count($this->lots_prolong)) {
      Doctrine_Query::create()
              ->update('Lot')
              ->set('active_till', '?', date('Y-m-d H:i:s', strtotime('+'.ParseTools::getLifetime($this->lot_options).' days')))
              ->whereIn('organization_link', $this->lots_prolong)
              ->andWhereIn('status', array('active', 'inactive'))
              ->addWhere('deleted = false')
              ->addWhere('DATE_FORMAT(active_till, ?) < ?', array('%Y%m%d', date('Ymd', strtotime('+'.ParseTools::getLifetime($this->lot_options).' days'))))
              ->execute();

      $this->lots_prolong = array();
    }
  }

  /**
   * Extract lot and page links
   * @param string $html
   * @return void|false
   */
  private function extractLinks($html) {
    // first find lot links
    preg_match_all('/href="(\/view\/info\/[^"]+)"/i', $html, $matches);
    foreach ($matches[1] as $i => $link) {
      $this->progress();
      $link = 'http://www.eip.ru'.$link;

      if (!in_array($link, $this->lots)) {
        $query = new Doctrine_Query();
        $query->from('Lot')->where('organization_link = ?', $link);
        if (count($this->lots) == $this->limit) {
          return false;
        } elseif ($query->count()) {
          $this->lots_prolong[] = $link;
          if ($this->exists_exceptions++ > $this->exists_exceptions_limit) {
            return false;
          }
          continue;
        } else {
          $this->lots[] = $link;
        }
      }
    }

    // ... after extract page locations
    preg_match_all('/href="(\/view\/(?:living|country|commerce)\/[^"]+&amp;p=\d+)"/i', $html, $matches);
    foreach ($matches[1] as $link) {
      $this->progress();

      if ($link) {
        if ($this->lot_options['type'] == 'apartament-sale') {
          $link = str_replace('кв.,ксд', '%EA%E2.,%EA%F1%E4', htmlspecialchars_decode($link));
        }
        elseif ($this->lot_options['type'] == 'commercial-sale' || $this->lot_options['type'] == 'commercial-rent') {
          $link = str_replace('ндв,офс,скл,обс,осз,кзу,кн.,гар',
                  '%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0',
                  htmlspecialchars_decode($link));
        }
        else {
          $link = htmlspecialchars_decode($link);
        }

        $link = 'http://www.eip.ru'.$link;
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
    preg_match('/id="FVyborka"(.+?)<\/form>/is', $html, $matches);
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
    preg_match('/class="ObjectBaseTbl"(.+?)id="bannermain"/is', $html, $matches);
    if (empty($matches[1])) {
      return null;
    }

    return $matches[1];
  }
}