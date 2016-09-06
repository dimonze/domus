<?php

require dirname(__FILE__).'/../helper/WordHelper.php';
require dirname(__FILE__).'/../AddressHelper.class.php';


abstract class BaseFetcher extends Fetcher
{
  public
    $lots_parsed = 0,
    $lots_fetched = 0;
  protected
    $parser = null,
    $conn = null,
    $address_helper = null,
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
   * @return BaseFetcher
   */
  public function __construct($options)
  {
    ProjectConfiguration::registerSimpleHtmlDom();
    $this->limit = $options['limit'];
    $this->pages[] = $options['url'];
    $this->lot_options = $options['data'];
    $this->conn = ProjectConfiguration::getActive()->getSlaveConnection();
    $this->parser = new SimpleHTMLDOM();
    $this->address_helper = new AddressHelper();

    return $this;
  }


  /**
   * Main function
   * @return void
   */
  public function get()
  {
    $this->getLocations();
    $this->lots_fetched = count($this->lots);
    ParseLogger::writeStart($this->lots_fetched);

    if ($this->lots_fetched) {
      foreach ($this->lots as $i => $url) {
        $this->progress(sprintf('Importing lot %d/%d.', $i+1, count($this->lots)));

        try {
          $data = $this->importLot($url);
          if (!$data || empty($data['organization_link'])) continue;

          if ($this->lot_options['region_id'] != $data['region_id']) {
            $data['region_id'] = $this->lot_options['region_id'];
            switch ($this->lot_options['region_id']) {
              case 47:  $this->lot_options['region_id'] = 78; break;
              case 50:  $this->lot_options['region_id'] = 77; break;
              case 77:  $this->lot_options['region_id'] = 50; break;
              case 78:  $this->lot_options['region_id'] = 47; break;
            }
          }

          if ($data['status'] != 'active') {
            ParseLogger::writeError($url, ParseLogger::BAD_STATUS);
            continue;
          }

          $check_query = $this->conn->prepare('SELECT COUNT(*) FROM `lot` WHERE `organization_link` = ?');
          $check_query->execute(array($data['organization_link']));
          if ($check_query->fetchColumn() > 0) {
            ParseLogger::writeError($url, ParseLogger::EXISTS);
            $check_query->closeCursor();;
            unset($check_query);
            continue;
          }
          $check_query->closeCursor();;
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

          if (!$params = ParseTools::doFilter($data, $params)) continue;

          if ($data['type'] == 'commercial-rent' && isset($params[53])) {
            $data['price'] = $params[53];
          }
          $field = $params['field'];
          unset($params['field']);


          if (empty($data['latitude']) || empty($data['longitude'])) {
            if (empty($data['address2'])) {
              $address = $data['address1'];
              if (!mb_stripos($address, ',')) {
                $geodata['lat'] = $geodata['lng'] = null;
              }
              elseif (!$geodata = ParseTools::getLatLngByAddress($address)) {
                ParseLogger::writeError($url, ParseLogger::EMPTY_GEODATA, $address);
                continue;
              }
            }
            else {
              $address = $data['address1'].', '.$data['address2'];
              try {
                $geodata = ParseTools::getLatLngByAddress($address);
              }
              catch (Exception $e) {}

              $address = $data['address1'];
              if (!mb_stripos($address, ',')) {
                $geodata['lat'] = $geodata['lng'] = null;
              }
              elseif (!$geodata = ParseTools::getLatLngByAddress($address)) {
                ParseLogger::writeError($url, ParseLogger::EMPTY_GEODATA, $address);
                continue;
              }
            }

            $data['latitude']  = $geodata['lat'];
            $data['longitude'] = $geodata['lng'];
          }
          if ((empty($data['latitude']) || empty($data['longitude'])) && $data['type'] != 'cottage-sale') {//для коттеджей можно сохранять без координат
            ParseLogger::writeError($url, ParseLogger::BAD_ADDRESS, $data['address1'].', '.$data['address2']);
            continue;
          }
          /*if (empty($data['latitude']) || empty($data['longitude'])) {
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
          }
          else {
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

          if (!$data = ParseTools::handleAddress($data, $geodata)) continue;*/

          if (!in_array($data['type'], array('new_building-sale', 'cottage-sale'))) {//дубликаты не проверяем у новостроек и коттеджей
            if (!ParseTools::removeDublicates($data, isset($params[$field]) ? $params[$field] : null)) {
              ParseLogger::writeError($url, ParseLogger::NEWER_EXISTS);
              continue;
            }
          }
          else {
            $data['status'] = 'inactive';//новостройки и коттеджи неактивны по-умолчанию
            $data['hidden_description'] = $data['description']; #11481
            unset($data['description']);
          }

          $brief = array('type' => $data['type']);
          foreach ($params as $id => $param) {
            $brief['field'.$id] = $param;
          }
          $data['brief'] = DynamicForm::makeBrief($brief);

          $lot = new Lot();
          $lot->fromArray($data);
          $lot->save();

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
            $images = $this->storeImages($photos, $lot);
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
          if (isset($lot) && !$lot->isNew()) {
            try {
              $lot->delete();
              $lot->free();
              unset($lot);
            }
            catch (Exception $e) {}
          }

          if (isset($lot)) $lot->free();
          unset($lot, $data, $params, $photos);

          if ($e->getMessage() == 'Geocoder limit reached') return;
          continue;
        }
      }
    }
  }


  /**
   * Parse lot data
   * @param array $data
   * @return array
   */
  protected function parseLotData(array $data)
  {
    if (empty($data['date'])) $data['date'] = date('Y-m-d H:i:s', strtotime('-'.mt_rand(1, 3).' days'));

    foreach ($data as $key => $value) {
      $this->progress();

      $method = sprintf('parseLot%s', sfInflector::camelize($key));
      if ($key != 'params' && method_exists($this, $method)) {
        $value = $this->$method($value);
      }

      switch ($key) {
        case 'title':
          $parsed = array_merge($parsed, $this->parseLotAddress($value));

          $address_parts = array();
          foreach (array('address1','address2') as $a) {
            if (!empty($parsed[$a])) {
              $parsed[$a] = preg_replace('/\s+/', ' ', $parsed[$a]);
              $parsed[$a] = trim($parsed[$a], ', ');
              $address_parts[] = $parsed[$a];
            }
          }

          $parsed['address_info'] = $this->address_helper->parseAddress(implode(', ', $address_parts));
          break;

        case 'description':
          $value = preg_replace('/(\s*<(br|p).*>\s*)+/isU', ' ', $value);
          $value = preg_replace('/(<a.+<\/a>)|(<.+>)/isU', ' ', $value);
          $value = preg_replace('/<.+>/iU', '', $value);
          $value = preg_replace('/http:\/\/[^\s]+/is', '', $value);
          $value = preg_replace('/w{0,3}\.*[a-z0-9-\.]+\.[a-z\.]{2,6}/isu', '', $value);
          $value = preg_replace('/((e-*mail|е-*м[ае][ий]л)(:|\.|\s*-))*\s*([a-z0-9_\.-]+)@([a-z0-9_\.-]+)\.([a-z\.]{2,6})\.*/isu', '', $value);
          $value = preg_replace('/&nbsp;/', ' ', $value);
          $value = preg_replace('/&[^;]+;/', '', $value);
          $value = preg_replace('/\s+/', ' ', $value);
          $value = preg_replace('/\s+,/', ',', $value);
          $value = preg_replace('/^\s+|\s+$/m', '', $value);
          //$value = trim($value, '  ');
          $parsed['description'] = mb_substr($value, 0, 1500, 'utf-8');
          break;

        case 'date':
          $parsed['created_at']   = date('Y-m-d H:i:s', strtotime($value));
          $parsed['active_till']  = date('Y-m-d H:i:s', strtotime('+'.ParseTools::getLifetime($this->lot_options).' days'));
          $parsed['status']       = strtotime($parsed['active_till']) < time() ? 'inactive' : 'active';
          break;

        case 'organization_contact_phone':
          $value = preg_replace('/(\d)\s(\d)/', '$1$2', $value);
          $value = preg_replace('/\s+,/', ',', $value);
          $value = trim($value);
          if (mb_strlen($value) > 4) {
            $parsed['organization_contact_phone'] = $value;
          }
          break;

        case 'organization_contact_name':
          $value = preg_replace('/&[^;]+;/', ' ', $value);
          $value = preg_replace('/<[^>]+>/', '', $value);
          $value = preg_replace('/\s+/', ' ', $value);
          $value = trim($value);

          if (mb_stripos($value, ' Sob.ru') != false) {
            $value = '';
          }

          $parsed['organization_contact_name'] = mb_substr($value, 0, 150, 'utf-8');
          break;

        case 'latitude':
        case 'longitude':
          $parsed[$key] = mb_substr($value, 0, 10);
          $parsed[$key] = floatval($value);
          break;

        case 'params':
          $value = $this->parseLotParams($value);
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
   * @param array $params
   * @return array
   */
  protected function fixLotParams(array $params)
  {
    $query = Doctrine_Query::create($this->conn)
            ->select('f.id, f.label, f.value, f.type')
            ->from('FormField f')
            ->leftJoin('f.FormField f2 WITH f.id = f2.field_id')
            ->andWhere('f2.type = ?', $this->lot_options['type'])
            ->andWhere('f.label IS NOT NULL');

    $fields = array();
    foreach ($query->fetchArray() as $field) {
      $fields[$field['label']] = $field;
    }
    $query->free();
    unset($query);

    foreach ($params as $key => $value) {
      unset($params[$key]);
      if (!isset($fields[$key])) continue;
      $field = $fields[$key];
      $params[$field['id']] = $value;

      if ($key == 'Детали' || $key == 'Доп. строения') continue;

      if (in_array($field['id'], array(1,7,8,9,26,27,36,46,47))) {
        $params[$field['id']] = round($value, 2);
      }
      elseif (in_array($field['id'], array(3,4,35,72,73,84))) {
        $params[$field['id']] = intval($value);
      }
      elseif ($field['id'] == 5) {
        $params[$field['id']] = ParseTools::getBuiltYear($value);
        if (empty($params[$field['id']])) {
          unset($params[$field['id']]);
          continue;
        }
      }

      if (isset($field['type']) && in_array($field['type'], array('select','multiple'))) {
        $value = $this->translateParamValue($value, $field['id']);
        if ($value) {
          $params[$field['id']] = $value;
        }
        elseif (is_null($value)) {
          $params = $this->selectSimilar($field['value'], $params, $field['id']);
        }
        else {
          unset($params[$field['id']]);
        }
      }

      unset($field);
    }

    return $params;
  }


  /**
   * Store lot images
   * @param array $photos
   * @param object $lot
   * @return array
   */
  protected function storeImages($photos, $lot)
  {
    $images   = array();
    $counter  = 0;
    $source   = sprintf('%s/%s/source', sfConfig::get('sf_web_dir'), sfConfig::get('app_upload_tmp_dir'));

    foreach ($photos as $i => $image) {
      if ($counter > 7) break;

      $this->progress();
      $filename = $lot->id.'_'.($i+1);

      $c = 0;
      do {
        $raw = @file_get_contents($image, 0, ParseTools::getStreamContext());
      } while (!$raw && ++$c < 2);

      $image = "$source/$filename";
      if ($raw && file_put_contents($image, $raw)) {
        chmod($image, 0666);

        $info   = getimagesize($image);
        $width  = $info[0];
        $height = $info[1];
        switch ($info['mime']) {
          case 'image/gif':   $ext = '.gif';  break;
          case 'image/jpeg':  $ext = '.jpg';  break;
          case 'image/png':   $ext = '.png';  break;
          default:            $ext = null;
        }

        if (!$ext || $height < 300) {
          unlink($image);
          continue;
        }

        if (method_exists($this, 'cropLotImage')) {
          if (!$this->cropLotImage($image, $width, $height)) {
            unlink($image);
            continue;
          }
        }

        $filename = ($i+1).$ext;
        if (rename($image, $lot->full_image_path . '/' . $filename)) {
          chmod($lot->full_image_path .'/' . $filename, 0666);
          $images[] = $filename;
          $counter++;
        }
      }
    }

    return $images;
  }


  /**
   * Fetch lot urls
   * @return void
   */
  private function getLocations()
  {
    while (count($this->lots) < $this->limit) {
      $left = array_diff($this->pages, $this->pages_fetched);
      if (empty($left)) break;

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
            'only_body'             => false,
            'use_proxy'             => $this->limit > 10,
            'check_https_redirect'  => strpos($url, 'irr.ru') !== false,
          ));
      }
      catch (Exception $e) {
        printf('%s%s', $e->getMessage(), PHP_EOL);
        continue;
      }

      if (!$this->extractLinks($data)) break;
    }

    if (count($this->lots_prolong)) {
      $this->prolongLots($this->lots_prolong, ParseTools::getLifetime($this->lot_options));
      $this->lots_prolong = array();
    }
  }


  /**
   * Append link if not exists
   * @param string $link
   * @return boolean
   */
  protected function appendLotLink($link)
  {
    if (!in_array($link, $this->lots)) {
      $query = $this->conn->prepare('SELECT COUNT(*) FROM `lot` WHERE `organization_link` = ?');
      $query->execute(array($link));

      if (count($this->lots) == $this->limit) {
        return false;
      }
      elseif ($query->fetchColumn() > 0) {
        $this->lots_prolong[] = $link;
        if ($this->exists_exceptions++ > $this->exists_exceptions_limit) {
          return false;
        }
      }
      else {
        $this->lots[] = $link;
      }

      $query->closeCursor();;
      unset($query);
    }

    return true;
  }


  /**
   * @param array $lot_ids
   * @param integer $nb_days
   * @return void
   */
  private function prolongLots($lot_ids, $nb_days)
  {
    Doctrine_Query::create()
            ->update('Lot')
            ->set('active_till', '?', date('Y-m-d H:i:s', strtotime('+'.$nb_days.' days')))
            ->whereIn('organization_link', $lot_ids)
            ->andWhereIn('status', array('active', 'inactive'))
            ->addWhere('deleted = false')
            ->addWhere('active_till < ?', date('Y-m-d 23:59:59', strtotime('+'.$nb_days.' days')))
            ->execute();
  }


  /**
   * Gets valid similar value of field from DB
   * @param string $types
   * @param array $params
   * @param int $field_id
   * @param string $default
   * @return array
   */
  private function selectSimilar($types, $params, $field_id, $default = false) {
    $types = preg_split('/\n/', $types);
    $max = 0;

    foreach($types as $i => $type) {
      similar_text($type, $params[$field_id], $percent);
      if ($percent > $max) {
        $max = $percent;
        $k = $i;
      }
    }

    if ($max > 70) {
      $params[$field_id] = $types[$k];
    }
    elseif ($default) {
      $params[$field_id] = $default;
    }
    else {
      unset($params[$field_id]);
    }

    return $params;
  }


  /**
   * Parse lot data
   * @param string $url
   * @return array | false
   */
  abstract protected function importLot($url);

  /**
   * Extract lot and page links
   * @param string $html
   * @return void | false
   */
  abstract protected function extractLinks($html);

  /**
   * Fix lot additional params
   * @param array $params
   * @return array $params
   */
  abstract protected function parseLotParams(array $params);

  /**
   * @param string $value
   * return string | false | void
   */
  abstract protected function translateParamValue($value);
}
