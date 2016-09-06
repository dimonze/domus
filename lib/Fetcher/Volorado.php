<?php

require dirname(__FILE__).'/../helper/WordHelper.php';

/**
 * Class for fetching http://volorado.ru/ lots
 *
 * @author Grechko Aleksey
 */
class Fetcher_Volorado extends Fetcher
{
  public
    $lots_parsed = 0,
    $lots_fetched = 0;
  protected
    $progress_callable = null;
  private
    $conn = null,
    $limit = null,
    $pages = array(),
    $pages_fetched = array(),
    $lots = array(),
    $lots_data = array(),
    $lot_options = array(),
    $lots_prolong = array(),

    $exists_exceptions = 0,
    $exists_exceptions_limit = 100;

  /**
   * Constructor
   * @param array $options
   * @param callable $progress_callable = null
   * @return Fetcher_Volorado
   */
  public function __construct($options, $progress_callable = null) {
    $this->limit = $options['limit'];
    $this->pages[] = $options['url'];
    $this->lot_options = $options['data'];
    $this->conn = ProjectConfiguration::getActive()->getSlaveConnection();
    if ($progress_callable !== null) {
      $this->progress_callable = $progress_callable;
    }

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
          if ($data['status'] != 'active') {
            ParseLogger::writeError($url, ParseLogger::BAD_STATUS);
            continue;
          }

          $check_query = Doctrine_Query::create($this->conn);
          $check_query->from('Lot')->where('organization_link = ?', $data['organization_link']);
          if ($check_query->count()) {
            ParseLogger::writeError($url, ParseLogger::EXISTS);
            continue;
          }
          $check_query->free();
          unset($check_query);

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

            $info->free();
            unset($info);
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
          $lot->free();
          unset($lot, $data, $params, $photos);

          $this->lots_parsed++;
        }
        catch (Exception $e) {
          ParseLogger::writeError($url, ParseLogger::EXCEPTION, $e->getMessage());
          if ($need_delete) {
            try {
              $lot->delete();
              $lot->free();
              unset($lot, $data, $params, $photos);
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
          'cleanup'               => array($this, 'cleanupData'),
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
    $data['date'] = $this->lots_data[$n]['date'];
    if (!isset($data['params'])) {
      $data['params'] = array();
    }

    preg_match('/Цена:\s*([\d\s.,]+)/i', $html, $matches);
    if (!empty($matches[1])) {
      $data['price'] = preg_replace('/[^\d.]/', '', str_replace(',', '.', $matches[1]));
    }
    if (empty($data['price']) || !($data['price'] > 0)) {
      ParseLogger::writeError($url, ParseLogger::EMPTY_PRICE);
      return false;
    }
    if (mb_strpos($this->lot_options['type'], 'sale') !== false && $data['price'] > 500000000) {
      $data['price'] /= 1000;
    } elseif (mb_strpos($this->lot_options['type'], 'rent') !== false && $data['price'] > 1000000) {
      $data['price'] /= 1000;
    }
    if ($this->lot_options['type'] == 'commercial-rent') {
      $data['params']['Арендная ставка кв.м/год'] = $data['price'];
    }

    if ($this->lot_options['type'] == 'commercial-sale' || $this->lot_options['type'] == 'commercial-rent'
           && empty($data['params']['Тип недвижимости'])) {
      preg_match('/<strong>Тип помещения:<\/strong>\s*([^<]+?)\s*<\/td>/is', $html, $matches);
      if (!empty($matches[1])) {
        $data['params']['Тип недвижимости'] = ParseTools::matchCommercialType($matches[1]);
      } else {
        ParseLogger::writeError($url, ParseLogger::COMM_TYPE);
        return false;
      }
    } elseif ($this->lot_options['type'] == 'apartament-sale' || $this->lot_options['type'] == 'apartament-rent'
            && empty($data['params']['Тип предложения'])) {
      if (preg_match('/id="green"><h1>[^<]*комнат(а|у)[^<]+<\/h1>/iu', $html)) {
        $data['params']['Тип предложения'] = 'комната';
      } else {
        preg_match('/<strong>Количество комнат:<\/strong>\s*(\d+)\s*<\/td>/i', $html, $matches);
        if (!empty($matches[1])) {
          $data['params']['Тип предложения'] = $matches[1].ending($matches[1], '', '-х', '-ти', '-ми').' комнатная квартира';
        }
      }
      if (empty($data['params']['Тип предложения'])) {
        ParseLogger::writeError($url, ParseLogger::ROOMS_NUM);
        return false;
      }
    }

    preg_match('/<strong>Город:<\/strong>\s*([^<]+?)\s*<\/td>/is', $html, $matches);
    if (!empty($matches[1])) {
      $data['title']['city'] = $matches[1];
    }
    preg_match('/<strong>Район:<\/strong>\s*([^<]+?)\s*<\/td>/is', $html, $matches);
    if (!empty($matches[1])) {
      $data['title']['raion'] = $matches[1];
    }
    preg_match('/<strong>Адрес:<\/strong>\s*([^<]+?)\s*<\/td>/is', $html, $matches);
    if (!empty($matches[1])) {
      $data['title']['street'] = $matches[1];
    }
    if (empty($data['title']['city'])) {
      ParseLogger::writeError($url, ParseLogger::EMPTY_ADDRESS);
      return false;
    }

    preg_match_all('/(?:Тел\.*|Моб\.*):\s*([^<]+?)\s*</is', $html, $matches);
    if (!empty($matches[1])) {
      $data['organization_contact_phone'] = implode(', ', array_unique($matches[1]));
    }

    preg_match('/<text class="prodname">\s*([^<@]+?)\s*<\/text>/is', $html, $matches);
    if (!empty($matches[1])) {
      $data['organization_contact_name'] = $matches[1];
    }

    preg_match('/<h1>Описание<\/h1>\s*<div class="txt_main">\s*(.+?)\s*<\/div>/is', $html, $matches);
    if (!empty($matches[1])) {
      $data['description'] = $matches[1];
    }
    if (empty($data['organization_contact_phone']) && !empty($data['description'])) {
      preg_match('/(?:Тел(?:ефон|\.)*|Т\.):*\s*([0-9-+()]+[0-9-+(), ]*)/isu', $data['description'], $matches);
      if (!empty($matches[1]) && mb_strlen($matches[1], 'utf-8') > 5) {
        $data['organization_contact_phone'] = $matches[1];
      }
    }
    if (empty($data['organization_contact_phone'])) {
      ParseLogger::writeError($url, ParseLogger::EMPTY_PHONE);
      return false;
    }

    preg_match_all('/src="(\/images\/catalog_images\/\d[^"]+)"/i', $html, $matches);
    if (!empty($matches[1])) {
      $data['photos'] = array_unique($matches[1]);
    }

    preg_match_all('/class="txt_main"><strong>([^<:]+):<\/strong>\s*(.*?)<\/td>/is', $html, $matches);
    if (!empty($matches[1])) {
      foreach ($matches[1] as $i => $param_name) {
        $data['params'][$param_name] = $matches[2][$i];
      }
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
          $parsed = array_merge($parsed, $this->parseAddress($value));
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
            $parsed['photos'][] = 'http://volorado.ru'.$item;
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
      if (in_array($key, array('Город',
                              'Район',
                              'Адрес',
                              'Количество помещений',
                              'Всего комнат в кв.',
                              'Тип помещения',
                              'Входная дверь',
                              'Ограждение',
          ))) {
        continue;
      }

      if ($key == 'Этаж') {
        $value = preg_replace('/\D/', '', $value);
      } elseif ($key == 'Этажность дома') {
        $key = 'Этажность';
        $value = preg_replace('/\D/', '', $value);
      } elseif ($key == 'Количество комнат') {
        $value = preg_replace('/\D/', '', $value);
      } elseif ($key == 'Площадь комнаты' && isset($params['Тип предложения']) &&  $params['Тип предложения'] == 'комната') {
        $key = 'Общая площадь';
      } elseif ($key == 'Окна') {
        $value = mb_strtolower($value, 'utf-8');
      }

      if (in_array($key, array('Общая площадь', 'Жилая площадь', 'Площадь кухни'))) {
        $value = preg_replace('/<sup>.+<\/sup>/', '', $value);
        $value = preg_replace('/[^\d.]/', '', $value);
      }

      if ($key == 'Площадь' || $key == 'Площадь участка') {
        $key = 'Площадь участка';
        preg_match('/^(\d+\.*\d*)/', $value, $matches);
        if (!empty($matches[1])) {
          $value = $matches[1];
        } else {
          continue;
        }

        if (mb_strpos($this->lot_options['type'], 'commercial') !== false) {
          $key = 'Общая площадь земли';
        }
      }

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
        }

        $value = mb_strtolower($value, 'utf-8');
      }

      if ($key == 'Тип дома' || $key == 'Материал дома' && mb_strpos($this->lot_options['type'], 'house') === false) {
        $key = 'Тип здания';
      } elseif ($key == 'Материал дома' && mb_strpos($this->lot_options['type'], 'house') !== false) {
        $key = 'Тип дома';
      }

      if ($key == 'Санузел') {
        $value = mb_strtolower($value, 'utf-8');
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
      if ($key == 'Бытовая техника') {
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
  private function fixLotParams(array $params) {
    foreach ($params as $key => $value) {
      $query = Doctrine_Query::create($this->conn);
      $field = $query->select('f.field_id, f.type, f2.value, f2.type')
          ->from('FormItem f')
          ->leftJoin('f.FormField f2 WITH f.field_id = f2.id')
          ->where('f2.label LIKE ?', $key)
          ->fetchArray();

      $query->free();
      unset($query);

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
          case 'хорошее':
          case 'удовлетворительное':
          case 'плохое':
            unset($params[$field[$i]['field_id']]);
            break;
          case 'требует косм. ремонт':
            $params[$field[$i]['field_id']] = 'требует косметического ремонта';
            break;
          case 'требует кап. ремонта':
            $params[$field[$i]['field_id']] = 'требует кап ремонта';
            break;
          case 'отличное':
            $params[$field[$i]['field_id']] = 'в отличном состоянии';
            break;
          case 'дерево':
            $params[$field[$i]['field_id']] = 'деревянные';
            break;
          case 'пластик':
            $params[$field[$i]['field_id']] = 'пластиковые стеклопакет';
            break;
          default:
            $params = ParseTools::selectSimilar($field[$i]['FormField']['value'], $params, $field[$i]['field_id']);
        }
      }

      unset($field);
    }

    return $params;
  }


  private function calcRentRate($params) {
    if (!empty($params['Общая площадь помещения'])) {
      $params['Арендная ставка кв.м/год'] = (intval($params['Арендная ставка кв.м/год']) / floatval($params['Общая площадь помещения']))*12;
      $params['Арендная ставка кв.м/год'] = ceil($params['Арендная ставка кв.м/год']);
    } elseif (!empty($params['Общая площадь земли'])) {
      $params['Арендная ставка кв.м/год'] = (intval($params['Арендная ставка кв.м/год']) / floatval($params['Общая площадь земли']))*12;
      $params['Арендная ставка кв.м/год'] = ceil($params['Арендная ставка кв.м/год']);
    }
    return $params;
  }

  /**
   * Parse address row
   * @param string $value
   * @return array $address
   */
  private function parseAddress($value)
  {
    $address1 = 'Волгоградская обл.';
    $address2 = '';


    if (mb_strpos($value['city'], ',') !== false) {
      $tmp_arr = explode(',', $value['city'], 2);
      if (empty($value['raion'])) {
        $value['raion'] = trim($tmp_arr[1]);
      } else {
        $value['raion'] .= ', '.trim($tmp_arr[1]);
      }

      $value['city'] = trim($tmp_arr[0]);
    }

    $address1 .= ', '.$value['city'];

    if (!empty($value['street'])) {
      $value['street'] = trim(preg_replace('/[^,]*'.$value['city'].',*\s*/isu', '', $value['street']));
    }

    if (!empty($value['street'])) {
      if (empty($address2)) {
        $address2 = $value['street'];
      } else {
        $address2 .= ', '.$value['street'];
      }
    } elseif (!empty($value['raion'])) {
      $address2 = 'район '.$value['raion'];
    }

    $data = array(
      'address1'      => $address1,
      'address2'      => $address2,
      'address_info'  => null,
    );

    return $data;
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
            'cleanup'               => array($this, 'cleanupData'),
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
    preg_match_all('/class="txt_inpup">([^<]+)<\/td>.+?<a[^>]+href="([^"]+)"[^>]*>Подробнее<\/a>/is', $html, $matches);
    foreach ($matches[2] as $i => $link) {
      $this->progress();

      if (preg_match('/Сегодня/', $matches[1][$i])) {
        $today = date('Y-m-d');
        $date = str_replace('Сегодня', $today, $matches[1][$i]);
      } elseif (preg_match('/Вчера/', $matches[1][$i])) {
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $date = str_replace('Вчера', $yesterday, $matches[1][$i]);
      } else {
        $year = date('Y');
        $replace_what = array(
            'Янв.',       'Фев.',
            'Мар.',       'Апр.',
            'Май',        'Июн.',
            'Июл.',       'Авг.',
            'Сен.',       'Окт.',
            'Ноя.',       'Дек.'
        );
        $replace_with = array(
            'january '.$year,    'february '.$year,
            'march '.$year,      'april '.$year,
            'may '.$year,        'june '.$year,
            'july '.$year,       'august '.$year,
            'september '.$year,  'october '.$year,
            'november '.$year,   'december '.$year
        );

        $date = str_replace($replace_what, $replace_with, $matches[1][$i]);
      }
      if (strtotime($date) > time()) continue;
      $active_till = strtotime(date('Y-m-d H:i:s', strtotime('+'.ParseTools::getLifetime($this->lot_options).' days', strtotime($date))));
      $link = 'http://volorado.ru'.$link;

      if (!in_array($link, $this->lots)) {
        $query = Doctrine_Query::create($this->conn);
        $query->from('Lot')->where('organization_link = ?', $link);
        if (count($this->lots) == $this->limit) {
          return false;
        } elseif ($query->count()) {
          $this->lots_prolong[] = $link;
          if ($this->exists_exceptions++ > $this->exists_exceptions_limit) {
            return false;
          }
        } elseif ($active_till > time()) {
          $n = array_push($this->lots, $link);
          $this->lots_data[$n-1] = array('date' => $date);
        }

        $query->free();
        unset($query);
      }
    }

    // ... after extract page locations
    preg_match_all('/href="([^"]+&page=\d+)"/is', $html, $matches);
    foreach ($matches[1] as $link) {
      $this->progress();

      if ($link) {
        $link = 'http://volorado.ru'.$link;
        if (!in_array($link, $this->pages)) {
          $this->pages[] = $link;
        }
      }
    }

    return true;
  }

  /**
   * Cleanup data for lot and list
   * @param string $html
   * @return string|null
   */
  protected function cleanupData($html) {
    preg_match('/id="main_content"(.+?)<\/form>/is', $html, $matches);
    if (empty($matches[1]) || preg_match('/<div class="not_found">/i', $html)) {
      return null;
    }
    return $matches[1];
  }
}