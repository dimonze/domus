<?php

require dirname(__FILE__).'/../helper/WordHelper.php';

/**
 * Class for fetching http://www.bsn.ru/ lots
 *
 * @author Grechko Aleksey
 */
class Fetcher_Bsn extends Fetcher
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
    $lot_options = array(),
    $lots_prolong = array(),

    $exists_exceptions = 0,
    $exists_exceptions_limit = 100,
    
    $months = array(
      '01' => 'jan', '02' => 'feb', '03' => 'mar', '04' => 'apr',
      '05' => 'may', '06' => 'jun', '07' => 'jul', '08' => 'aug',
      '09' => 'sep', '10' => 'oct', '11' => 'nov', '12' => 'dec',
    );

  /**
   * Constructor
   * @param array $options
   * @param callable $progress_callable = null
   * @return Fetcher_Bsn
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
      foreach ($this->lots as $i => $url) {
        $this->progress(sprintf('Importing lot %d/%d.', $i+1, count($this->lots)));
        $need_delete = false;
        try {
          $data = $this->importLot($url, $i);
          if (!$data || empty($data['organization_link'])) continue;

          if ($this->lot_options['region_id'] != $data['region_id']) {
            $data['region_id'] = $this->lot_options['region_id'];
            if ($this->lot_options['region_id'] == 47) {
              $this->lot_options['region_id'] = 78;
            }
          }
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

    preg_match('/estateoffer_price[^"]*"[^>]*>\s*<div[^>]*>([^<]+)</is', $html, $matches);
    if (!empty($matches[1])) {
      $data['price'] = floatval(preg_replace('/\D/', '', $matches[1]));
    }
    if (empty($data['price']) || !($data['price'] > 0)) {
      ParseLogger::writeError($url, ParseLogger::EMPTY_PRICE);
      return false;
    } elseif ($this->lot_options['type'] == 'commercial-rent') {
      $data['params']['Арендная ставка кв.м/год'] = $data['price'];
    }

    preg_match('/Дата обновления варианта: ([^<]+)/is', $html, $matches);
    if (!empty($matches[1])) {
      preg_match('/\.(\d+)\./', $matches[1], $match);
      if (!empty($match[1])) {
        $matches[1] = preg_replace('/\.(\d+)\./', sprintf(' %s ', $this->months[$match[1]]), $matches[1]);
      }
      
      $data['date'] = date('Y-m-d H:i:s', strtotime($matches[1]));
    } else {
      $data['date'] = date('Y-m-d H:i:s', strtotime('-'.mt_rand(0, 723).' minute'));
    }

    preg_match('/estateoffer_top[^>]+>\s*<div[^>]+>(.+?)<\/div>/is', $html, $matches);
    if (!empty($matches[1])) {
      $params = explode(',', $matches[1]);

      foreach ($params as $param) {
        if (preg_match('/\dк в \dккв/i', $param)) {
          $data['params']['Тип предложения'] = 'комната';
          continue;
        }
        preg_match('/(\d)ккв/i', $param, $matches);
        if (!empty($matches[1])) {
          $data['params']['Тип предложения'] = $matches[1].ending($matches[1], '', '-х', '-ти', '-ми').' комнатная квартира';
          continue;
        }
        preg_match('/([^<]+)<[^>]*sup[^>]*>/i', $param, $matches);
        if (!empty($matches[1])) {
          $data['params']['Общая площадь'] = floatval(preg_replace('/[^\d.]/', '', $matches[1]));
          continue;
        } else {
          preg_match('/<div[^>]+>- жилая:*<\/div>\s*<\/td>\s*<td[^>]+>\s*<div[^>]+>\s*([^<]+)/i', $html, $matches);
          if (!empty($matches[1])) {
            $data['params']['Общая площадь'] = floatval(preg_replace('/[^\d.]/', '', $matches[1]));
          }
        }
        preg_match('/([^<]+)\sсот\./i', $param, $matches);
        if (!empty($matches[1])) {
          $data['params']['Площадь участка'] = floatval(preg_replace('/[^\d.]/', '', $matches[1]));
          continue;
        }
        preg_match('/ ([\d\s.]+) Га\s*$/i', $param, $matches);
        if (!empty($matches[1])) {
          $data['params']['Площадь земельного участка'] = floatval(preg_replace('/[^\d.]/', '', $matches[1]));
          continue;
        }
        preg_match('/этаж (\d+ из \d+)/i', $param, $matches);
        if (!empty($matches[1])) {
          $data['params']['Этаж'] = str_replace(' из ', '/', $matches[1]);
          continue;
        }
        preg_match('/этаж (\d+)/i', $param, $matches);
        if (!empty($matches[1])) {
          $data['params']['Этаж'] = $matches[1];
          continue;
        }
        preg_match('/тип дома (.+)$/i', $param, $matches);
        if (!empty($matches[1])) {
          $data['params']['Тип дома'] = trim($matches[1]);
          continue;
        }

        if ($this->lot_options['type'] == 'commercial-sale' || $this->lot_options['type'] == 'commercial-rent') {
          $data['params']['Тип недвижимости'] = ParseTools::matchCommercialType($param);
        }
      }
    }

    preg_match('/>\s*Адрес:\s*<\/div>.+?padding[^>]+>\s*<\/div>\s*<\/td>\s*<td[^>]+>(.+?)<\/div>/is', $html, $matches);
    if (!empty($matches[1])) {
      $matches[1] = preg_replace('/<\/*(?:div|a)[^>]*>/i', '', $matches[1]);
      $addresses = preg_split('/<br\s*\/*>/i', $matches[1]);

      foreach ($addresses as $address) {
        $address = preg_replace('/^\s+|\s*,\s*$/', '', $address);
        $address = str_replace('и&#774;', 'й', $address);

        if (mb_strpos($address, 'район ', null, 'utf-8') !== false) {
          $data['title']['raion'] = $address;
          if (in_array($data['title']['raion'], array('район Выборгский СПб', 'район Область', 'район СПб и пригороды'))) return false;
          continue;
        }

        if (preg_match('/<img .+/i', $address)) {
          $data['title']['metro'] = preg_replace('/<[^>]+>/', '', $address);
          if (($pos = mb_strpos($data['title']['metro'], ',', null, 'utf-8')) != false) {
            $data['title']['metro'] = mb_substr($data['title']['metro'], 0, $pos, 'utf-8');
          }
          $data['title']['metro'] = trim($data['title']['metro']);
        } elseif (preg_match('/(?:^|\s)мин(?:\.|ут)*(?:\s|$)/', $address) || preg_match('/(?:^|\s)ж\.*д\.*\s/', $address)) {
          continue;
        } else {
          $address = preg_replace('/\s*\+\s*\d+\s*(?:км)*.*$/is', '', $address);
          if (empty($data['title']['street'])) {
            $data['title']['street'] = $address;
          } else {
            $data['title']['street'] .= ', '.$address;
          }
        }
      }
    }
    if (empty($data['title']['street'])) {
      ParseLogger::writeError($url, ParseLogger::EMPTY_ADDRESS);
      return false;
    }

    preg_match('/>\s*Продавец:\s*<\/div>.+?padding:[^>]+>(.+?)<\/*div/is', $html, $matches);
    if (!empty($matches[1])) {
      $contacts = preg_split('/<br\s*\/*>/i', $matches[1]);
      $is_agency = false;
      foreach ($contacts as $contact) {
        if (preg_match('/<a[^>]*title="Подробнее о компании"[^>]*>/i', $contact)) {
          $is_agency = true;
        }
        elseif (preg_match('/<[^>]+>|w{0,3}\.*[a-z0-9-\.]+\.[a-z\.]{2,6}|Факс:/is', $contact) || $contact == 'Частные заявки') {
          continue;
        }
        
        if ($contact == 'Агентство') {
          $is_agency = true;
          continue;
        }
        
        if (preg_match('/Тел\.*:\s*(.+)/is', $contact)) {
          if (empty($data['organization_contact_phone'])) {
            $data['organization_contact_phone'] = trim(preg_replace('/Тел\.*:\s*/', '', $contact));
          } else {
            $data['organization_contact_phone'] .= ', '.trim(preg_replace('/Тел\.*:\s*/', '', $contact));
          }
          continue;
        }

        if ($is_agency) {
          $contact = 'Агентство '.$contact;
          $is_agency = false;
        }
        
        if (empty($data['organization_contact_name'])) {
          $data['organization_contact_name'] = preg_replace('/<[^>]+>|&[^;]+;/is', '', $contact);
        } else {
          $data['organization_contact_name'] .= ', '.preg_replace('/<[^>]+>|&[^;]+;/is', '', $contact);
        }
      }
    }

    preg_match('/>\s*Примечание:\s*<\/div>.+?padding[^>]+>(.+?)<\/div>/is', $html, $matches);
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

    preg_match('/loadEstateMap\(\'*([\d.]+)\'*,\s*\'*([\d.]+)\'*/is', $html, $matches);
    if (!empty($matches[1]) && !empty($matches[2])) {
      $data['latitude']  = mb_substr($matches[1], 0, 10);
      $data['longitude'] = mb_substr($matches[2], 0, 10);
    }

    preg_match_all('/src="([^"]+\/images\/[^"]+)"/i', $html, $matches);
    if (!empty($matches[1])) {
      $data['photos'] = array_unique($matches[1]);
    }

    preg_match('/>\s*Общая информация:\s*<\/div>\s*<\/td>\s*<\/tr>(.+?)<td[^>]+estateoffer_title/is', $html, $matches_params);
    if (empty($matches_params[1])) {
      ParseLogger::writeError($url, ParseLogger::EMPTY_PARAMS);
      return false;
    }
    preg_match('/>\s*Площади:\s*<\/div>\s*<\/td>\s*<\/tr>(.+?)<td[^>]+estateoffer_title/is', $html, $matches_params_more);
    if (!empty($matches_params_more[1])) {
      $matches_params[1] .= $matches_params_more[1];
    }

    preg_match_all('/<div[^>]+>- ([^<]+?):*<\/div>.+?<div[^>]+>\s*(.*?)\s*<\/div>/is', $matches_params[1], $matches);
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
          $parsed = array_merge($parsed, $this->parseAddress($data, $value));
          break;

        case 'description':
          $value = preg_replace('/(\s*<(br|p).*>\s*)+/isU', ' ', $value);
          $value = preg_replace('/(<a.+<\/a>)|(<.+>)/isU', ' ', $value);
          $value = preg_replace('/<.+>/iU', '', $value);
          $value = preg_replace('/http:\/\/[^\s]+/is', '', $value);
          $value = preg_replace('/w{0,3}\.*[a-z0-9-\.]+\.[a-z\.]{2,6}/isu', '', $value);
          $value = preg_replace('/((e-*mail|е-*м[ае][ий]л)(:|\.|\s*-))*\s*([a-z0-9_\.-]+)@([a-z0-9_\.-]+)\.([a-z\.]{2,6})\.*/isu', '', $value);
          $value = preg_replace('/Цены указаны в [^(руб)]+руб\./isu', '', $value);
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
            $parsed['photos'][] = $item;
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
      if (in_array($key, array('ID Варианта',
                              'вход',
                              'количество телефонных линий',
                              'пол',
                              'окна',
                              'горячая вода',
                              'комнат',
                              'кол-во соседей',
                              'материал крыши',
                              'питьевая вода',
                              'право на строение',
                              'прописка',
                              'состояние участка',
                              'целевое назначение',
                              'право на участок',
                              'ближайшая ЖД станция',
                              'расстояние до ближайшей ЖД станции',
                              'готовность',
                              ))) {
        continue;
      }
      $key = mb_strtoupper(mb_substr($key, 0, 1, 'utf-8'), 'utf-8').mb_substr($key, 1, mb_strlen($key, 'utf-8'), 'utf-8');

      if ($key == 'Кухни') {
        $key = 'Площадь кухни';
      } elseif ($key == 'Жилая') {
        $key = 'Жилая площадь';
      } elseif ($key == 'Участка') {
        $key = 'Площадь участка';
      }

      if ($key == 'Этаж') {
        $etazhi = explode('/', $value, 2);
        if (count($etazhi) > 1) {
          $checked['Этаж'] = preg_replace('/\D/', '', $etazhi[0]);
          $key = 'Этажность';
          $value = preg_replace('/\D/', '', $etazhi[1]);
        } else {
          $value = preg_replace('/\D/', '', $value);
        }
      }

      if ($key == 'Этажей' || $key == 'Количество этажей') {
        $key = 'Этажность';
      }

      if ($key == 'Площадь земельного участка') {
        preg_match('/^(\d+\.*\d*)/', $value, $matches);
        if (!empty($matches[1])) {
          $value = floatval($matches[1])*100;
        } else {
          continue;
        }

        if (mb_strpos($this->lot_options['type'], 'commercial') !== false) {
          $key = 'Общая площадь земли';
          $value /= 100;
        }
      } elseif ($key == 'Площадь участка') {
        preg_match('/^(\d+\.*\d*)/', $value, $matches);
        if (!empty($matches[1])) {
          $value = floatval($matches[1]);
        } else {
          continue;
        }
      }

      if ($key == 'Тип дома' || $key == 'Материал стен' && mb_strpos($this->lot_options['type'], 'house') === false) {
        $key = 'Тип здания';
      } elseif ($key == 'Материал стен' && mb_strpos($this->lot_options['type'], 'house') !== false) {
        $key = 'Тип дома';
      }
      
      if (in_array($key, array('Общая площадь', 'Площадь кухни', 'Жилая площадь'))) {
        if (mb_stripos($value, ' до ', null, 'utf-8') !== false) continue;
        
        $value = preg_replace('/\s*м<.+/is', '', $value);
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

      if ($key == 'Год постройки') {
        $value = preg_replace('/\D/', '', $value);
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
        if (!in_array($value, array('-','?','','нет'))) {
          $value = 'да';
        }
      }
      if ($key == 'Бытовая техника') {
        $key = 'Оборудование/бытовая техника';
        $value = mb_strtolower($value, 'utf-8');
        if (!in_array($value, array('-','?','','нет'))) {
          $value = 'да';
        }
      }

      if (in_array($key, array('Электричество','Водопровод'))) {
        $value = mb_strtolower($value, 'utf-8');
        if (!in_array($value, array('-','?','','нет'))) {
          $value = 'есть';
        }
      }

      if (in_array($key, array('Гараж','Баня'))) {
        $value = mb_strtolower($value, 'utf-8');
        
        if (!in_array($value, array('-','?','','нет'))) {
          $key = mb_strtolower($key, 'utf-8');
          
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
                               'Водоём',
                               'Охрана'
                              ))) {
        $key = mb_strtolower($key, 'utf-8');

        $value = mb_strtolower($value, 'utf-8');
        if (!in_array($value, array('-','?','','нет'))) {
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

      if (empty($value) || in_array($value, array('-','?',''))) {
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
          case 'Сталинка':
          case 'Сталинский':
          case 'ремонт':
          case 'удовлетворительное':
          case 'без отделки':
          case 'Старый фонд':
          case 'шлакоблочный':
            unset($params[$field[$i]['field_id']]);
            break;
          case 'Кирпично-монолитный':
            $params[$field[$i]['field_id']] = 'Монолитно-кирпичный';
            break;
          case 'Деревянный':
          case 'Брус':
          case 'бревна':
          case 'брус/бревно':
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
          case 'евростандарт':
            $params[$field[$i]['field_id']] = 'peмонт по западным стандартам';
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
  private function parseAddress($data, $value) {
    if ($this->lot_options['region_id'] != 78) {
      unset($value['metro']);
    }
    if (isset($value['raion']) && $value['raion'] == 'район Выборгский Лен.обл.') {
      $value['raion'] = 'район Выборгский';
    }

    $city = ParseTools::getRegionName($this->lot_options['region_id']);

    $address1 = $city;
    $address2 = '';

    if (!empty($value['metro'])) {
      $address1 .= ', м. '.$value['metro'];
    } elseif (!empty($value['raion'])) {
      $address1 .= ', '.$value['raion'];
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
            'strip_comments'  => true,
            'strip_html'      => true,
            'cleanup'         => array($this, 'cleanupDataList'),
            'method'          => 'POST',
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
    $post_params = mb_substr($this->pages[0], mb_strpos($this->pages[0], '?', null, 'utf-8'), mb_strlen($this->pages[0], 'utf-8'), 'utf-8');
    // first find lot links
    preg_match_all('/href="([^"]+)"[^>]+title="Подробнее">\d+<\/a/is', $html, $matches);
    foreach ($matches[1] as $i => $link) {
      $this->progress();
      $link = 'http://www.bsn.ru'.$link;

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
        } else {
          $this->lots[] = $link;
        }
        
        $query->free();
        unset($query);
      }
    }

    // ... after extract page locations
    preg_match_all('/document.hids.action = (?:\'|")([^\']+\/page\d+\/*)(?:\'|")[^>]+>\d+/is', $html, $matches);
    foreach ($matches[1] as $link) {
      $this->progress();

      if ($link) {
        $link = 'http://www.bsn.ru'.$link.$post_params;
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
    preg_match('/class="dtable"(.+)<\/form>/is', $html, $matches);
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
    preg_match('/div class="mcol">(.+)<div class="rcol"/is', $html, $matches);
    if (empty($matches[1])) {
      return null;
    }
    return $matches[1];
  }
}