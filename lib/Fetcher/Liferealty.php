<?php

require dirname(__FILE__).'/../helper/WordHelper.php';

/**
 * Class for fetching http://liferealty.ru/ lots
 *
 * @author Grechko Aleksey
 */
class Fetcher_Liferealty extends Fetcher
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
   * @return Fetcher_Liferealty
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
          if ($data['status'] != 'active') {
            ParseLogger::writeError($url, ParseLogger::BAD_STATUS);
            continue;
          }

          $check_query = Doctrine_Query::create(ProjectConfiguration::getActive()->getSlaveConnection());
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
          unset($lot);

          $this->lots_parsed++;
        }
        catch (Exception $e) {
          ParseLogger::writeError($url, ParseLogger::EXCEPTION, $e->getMessage());
          if ($need_delete) {
            try {
              $lot->delete();
              $lot->free();
              unset($lot);
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

    if (preg_match('/<span>Цена договорная<\/span>/i', $html)) {
      ParseLogger::writeError($url, ParseLogger::EMPTY_PRICE);
      return false;
    }

    preg_match('/<div class="card_price">(.+?)<span/is', $html, $matches);
    if (!empty($matches[1])) {
      $data['price'] = preg_replace('/[^\d.]/', '', str_replace(',', '.', $matches[1]));
    }
    if (empty($data['price']) || !($data['price'] > 0)) {
      ParseLogger::writeError($url, ParseLogger::EMPTY_PRICE);
      return false;
    } elseif ($this->lot_options['type'] == 'commercial-rent') {
      $data['params']['Арендная ставка кв.м/год'] = $data['price']*1000;
    } else {
      $data['price'] *= 1000;
    }

    preg_match('/<div class="card_date">[^<]+добавлено ([^<]+) года<\/div>/is', $html, $matches);
    if (!empty($matches[1])) {
      $months = array(
        'january' => 'января',      'february' => 'февраля',
        'march' => 'марта',         'april' => 'апреля',
        'may' => 'мая',             'june' => 'июня',
        'july' => 'июля',           'august' => 'августа',
        'september' => 'сентября',  'october' => 'октября',
        'november' => 'ноября',     'december' => 'декабря'
      );

      $matched = false;
      foreach ($months as $eng => $rus) {
        if (mb_strpos($matches[1], $rus, null, 'utf-8') !== false) {
          $matches[1] = str_replace($rus, $eng, $matches[1]).' 00:00:00';
          $matched = true;
          break;
        }
      }
      if (!$matched) {
        $data['date'] = date('Y-m-d H:i:s', strtotime('-'.mt_rand(0, 723).' minute'));
      } else {
        $data['date'] = date('Y-m-d H:i:s', strtotime($matches[1]));
      }
    } else {
      $data['date'] = date('Y-m-d H:i:s', strtotime('-'.mt_rand(0, 723).' minute'));
    }

    preg_match('/<h1 class="fleft">(?:Продается|Сдается) ([^<]+)<\/h1>/is', $html, $matches);
    if (!empty($matches[1])) {
      if ($this->lot_options['type'] == 'apartament-sale' || $this->lot_options['type'] == 'apartament-rent') {
        switch ($matches[1]) {
          case 'комната':
            $data['params']['Тип предложения'] = 'комната';
            break;
          case 'однокомнатная квартира':
            $data['params']['Тип предложения'] = '1 комнатная квартира';
            break;
          case 'двухкомнатная квартира':
            $data['params']['Тип предложения'] = '2-х комнатная квартира';
            break;
          case 'трехкомнатная квартира':
            $data['params']['Тип предложения'] = '3-х комнатная квартира';
            break;
          case 'четырехкомнатная квартира':
            $data['params']['Тип предложения'] = '4-х комнатная квартира';
            break;
          case 'пятикомнатная квартира (и более)':
            $data['params']['Тип предложения'] = '5-ти комнатная квартира';
            break;
        }
      } elseif ($this->lot_options['type'] == 'commercial-sale' || $this->lot_options['type'] == 'commercial-rent') {
        $data['params']['Тип недвижимости'] = ParseTools::matchCommercialType($matches[1]);
      }
    }

    preg_match('/Населенный пункт: (.+?)<br>/is', $html, $matches);
    if (!empty($matches[1])) {
      $data['title']['city'] = preg_replace('/<[^>]+>|&[^;]+;/is', '', $matches[1]);
    }
    preg_match('/Район: (.+?)<br>/is', $html, $matches);
    if (!empty($matches[1])) {
      $data['title']['raion'] = preg_replace('/<[^>]+>|&[^;]+;/is', '', $matches[1]);
    }
    preg_match('/Адрес: (.+?)<br>/is', $html, $matches);
    if (!empty($matches[1])) {
      $data['title']['street'] = preg_replace('/<[^>]+>|&[^;]+;/is', '', $matches[1]);
    }
    if (empty($data['title']['city'])) {
      ParseLogger::writeError($url, ParseLogger::EMPTY_ADDRESS);
      return false;
    }

    preg_match('/<div class="c_phone">(.+?)<\/div>/i', $html, $matches);
    if (!empty($matches[1])) {
      $data['organization_contact_phone'] = $matches[1];
    }

    preg_match('/<div class="c_face">(.+?)<span/is', $html, $matches);
    if (!empty($matches[1])) {
      $data['organization_contact_name'] = preg_replace('/<[^>]+>/is', '', $matches[1]);
    }

    preg_match('/<h4>Дополнительная информация<\/h4>(.+?)<\/div>/is', $html, $matches);
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

    preg_match_all('/fullSrc="([^"]+)"/i', $html, $matches);
    if (!empty($matches[1])) {
      $data['photos'] = array_unique($matches[1]);
    }

    preg_match('/<div class="card_block">\s*<h4>Параметры объекта<\/h4>(.+?)<\/div>/is', $html, $matches_params);
    if (empty($matches_params[1])) {
      ParseLogger::writeError($url, ParseLogger::EMPTY_PARAMS);
      return false;
    }

    preg_match_all('/\s*([^<>:]+): (.+?)(?:<br>|<\/p>|<span[^>]*>[^<]+<\/span>)/is', $matches_params[1], $matches);
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
          $parsed['created_at'] = $value;
          $parsed['active_till'] = date('Y-m-d H:i:s', strtotime('+'.ParseTools::getLifetime($this->lot_options).' days'));
          if (strtotime($parsed['active_till']) < time()) {
            $parsed['status'] = 'inactive';
          } else {
            $parsed['status'] = 'active';
          }
          break;

        case 'photos':
          if ($data['region_id'] == 61) {
            $link = 'http://rostov.life-realty.ru';
          } elseif ($data['region_id'] == 23) {
            $link = 'http://krasnodar.life-realty.ru';
          }

          foreach ($value as $i => $item) {
            $parsed['photos'][] = $link.$item;
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
      if (in_array($key, array('Состояние дома'))) {
        continue;
      }

      if ($key == 'Этаж') {
        $etazhi = explode('/', $value, 2);
        if (count($etazhi) > 1) {
          if ($etazh = preg_replace('/\D/', '', $etazhi[0])) {
            $checked['Этаж'] = $etazh;
          }
          $key = 'Этажность';
          $value = preg_replace('/\D/', '', $etazhi[1]);
        } else {
          $value = preg_replace('/\D/', '', $value);
        }
      }

      if ($key == 'Количество комнат') {
        $value = mb_strtolower($value, 'utf-8');
        switch ($value) {
          case 'одна':
            $value = 1;
            break;
          case 'две':
            $value = 2;
            break;
          case 'три':
            $value = 3;
            break;
          case 'четыре':
            $value = 4;
            break;
          case 'пять':
            $value = 5;
            break;
          case 'шесть':
            $value = 6;
            break;
          case 'семь':
            $value = 7;
            break;
          case 'восемь':
            $value = 8;
            break;
          default:
            continue;
        }
      }

      if ($key == 'Площадь') {
        $key = 'Общая площадь';
      }
      if ($key == 'Площадь помещения') {
        $key = 'Общая площадь';
        $value = preg_replace('/<sup>.+<\/sup>/', '', $value);
        $value = preg_replace('/[^\d.]/', '', $value);
      }

      if ($key == 'Общая площадь' && mb_strpos($this->lot_options['type'], 'commercial') === false) {
        if (isset($params['Тип предложения']) && $params['Тип предложения'] == 'комната' && !empty($params['Площадь комнаты'])) continue;

        $value = preg_replace('/<sup>.+<\/sup>/', '', $value);
        if (mb_strpos($value, '/') !== false) {
          $areas_names = array('Общая площадь', 'Жилая площадь', 'Площадь кухни');

          $areas = explode('/', $value, 3);
          foreach ($areas as $n => $val) {
            $val = preg_replace('/[^\d.]/', '', $val);
            if (!empty($val)) {
              $checked[$areas_names[$n]] = $val;
            }
          }

          if (isset($checked['Общая площадь'])) {
            $value = $checked['Общая площадь'];
          } else {
            continue;
          }
        } else {
          $value = preg_replace('/[^\d.]/', '', $value);
        }
      }
      if ($key == 'Площадь комнаты' && $params['Тип предложения'] == 'комната') {
        $key = 'Общая площадь';
        $value = preg_replace('/<sup>.+<\/sup>/', '', $value);
        $value = preg_replace('/[^\d.]/', '', $value);
      }

      if ($key == 'Площадь участка') {
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

      if ($key == 'Тип дома' || $key == 'Материал дома' && mb_strpos($this->lot_options['type'], 'house') === false) {
        $key = 'Тип здания';
      } elseif ($key == 'Материал дома' && mb_strpos($this->lot_options['type'], 'house') !== false) {
        $key = 'Тип дома';
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

      if ($key == 'Состояние помещения') {
        switch ($this->lot_options['type']) {
          case 'apartament-sale':
            $key = $key;
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
      $query = Doctrine_Query::create(ProjectConfiguration::getActive()->getSlaveConnection());
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
          case 'Вторичное жилье':
          case 'Сталинка':
          case 'Сталинский':
          case 'Ремонт':
          case 'ремонт':
          case 'удовлетворительное':
          case 'без отделки':
            unset($params[$field[$i]['field_id']]);
            break;
          case 'Кирпично-монолитный':
            $params[$field[$i]['field_id']] = 'Монолитно-кирпичный';
            break;
          case 'Деревянный':
          case 'Брус':
            $params[$field[$i]['field_id']] = 'Дерево';
            break;
          case 'частичная отделка':
          case 'строительная отделка':
            $params[$field[$i]['field_id']] = 'требует косметического ремонта';
            break;
          case 'хорошее':
            $params[$field[$i]['field_id']] = 'после косметического ремонта';
            break;
          case 'отличное':
            $params[$field[$i]['field_id']] = 'в отличном состоянии';
            break;
          default:
            $params = ParseTools::selectSimilar($field[$i]['FormField']['value'], $params, $field[$i]['field_id']);
        }
      }
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
    if ($this->lot_options['region_id'] == 77 && $value['city'] == 'Московская обл.') {
      $this->lot_options['region_id'] = 50;
    } elseif ($this->lot_options['region_id'] == 78 && $value['city'] == 'Ленинградская область') {
      $this->lot_options['region_id'] = 47;
    }

    $city = ParseTools::getRegionName($this->lot_options['region_id']);

    $address1 = '';
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

    if (!empty($value['street'])) {
      if (mb_strpos($value['street'], '/') !== false) {
        $value['street'] = mb_substr($value['street'], 0, mb_strpos($value['street'], '/', null, 'utf-8'), 'utf-8');
      }
      $value['street'] = trim(preg_replace('/'.$value['city'].',*\s*/isu', '', $value['street']));
      $value['street'] = preg_replace('/\d+\s*км\.* от [^,]+,*\s*/isu', '', $value['street']);
    }

    $address1 = $city.', '.$value['city'];

    if (!empty($value['raion'])) {
      $matched = false;
      if (!empty($value['street'])) {
        $tmp_raion = preg_replace('/(^|\s)[^\s,]{0,4}(\s|$)/isu', '', $value['raion']);
        $matched = preg_match('/'.$tmp_raion.'/isu', $value['street']);
      }

      if (!$matched) {
        if ($value['city'] != 'Ростов-на-Дону' && $value['city'] != 'Краснодар') {
          if (empty($address2)) {
            $address2 = $value['raion'];
          } else {
            $address2 .= ', '.$value['raion'];
          }
        } elseif (empty($value['street'])) {
          $address2 = 'район '.$value['raion'];
        }
      }
    }

    if (!empty($value['street'])) {
      if (empty($address2)) {
        $address2 = $value['street'];
      } else {
        $address2 .= ', '.$value['street'];
      }
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
    preg_match_all('/offerHref="([^"]+)"/is', $html, $matches);
    foreach ($matches[1] as $i => $link) {
      $this->progress();

      if (!in_array($link, $this->lots)) {
        $query = Doctrine_Query::create(ProjectConfiguration::getActive()->getSlaveConnection());
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

        $query->free();
        unset($query);
      }
    }

    // ... after extract page locations
    preg_match_all('/href="([^"]+&page=\d+)"/is', $html, $matches);
    foreach ($matches[1] as $link) {
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
    preg_match('/(<tr class="list_head".+?)life-realty\.ru\/(?:sale|country|commerce)\/hot\//is', $html, $matches);
    if (empty($matches[1])) {
      preg_match('/(<tr class="list_head".+?)<div class="right">/is', $html, $matches);
      if (empty($matches[1])) {
        return null;
      }
    }

    return $matches[1];
  }

  /**
   * Cleanup data for lot
   * @param string $html
   * @return string|null
   */
  protected function cleanupDataLot($html) {
    preg_match('/(id="list_(?:sale|rent)".+?)life-realty\.ru\/(?:sale|country|commerce)\/hot\//is', $html, $matches);
    if (empty($matches[1])) {
      preg_match('/(id="list_(?:sale|rent)".+?)<div class="right">/is', $html, $matches);
      if (empty($matches[1])) {
        return null;
      }
    }

    return $matches[1];
  }
}