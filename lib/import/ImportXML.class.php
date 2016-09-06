<?php

class ImportXML extends ImportFile
{
  protected static
    $lots_counter   = 0,
    $internal_id    = null,
    $log_id         = null,
    $_region_nodes  = null,
    $_metros        = null,
    $log            = null;

  static function mesto($lot_object)
  {
    try {
      if (!empty($lot_object['log'])) {
        self::$log_id = $lot_object['log'];
        $log = Doctrine::getTable('ImportLog')->find(self::$log_id);
        if ($log) {
          self::$log = $log;
        }
      }
      
      if (null === self::$log){
        throw new Exception(
          parent::$error_codes[parent::ERROR_IMPORT_LOG],
          parent::ERROR_IMPORT_LOG
        );
      }
      
      $data = array();
      $data['user_id']  = $lot_object['user_id'];
      
      $item = simplexml_load_string($lot_object['lot']);
      $attributes = $item->attributes();
      if (!empty($attributes['internal-id'])) {
        self::$internal_id = $data['internal_id'] 
                           = $attributes['internal-id']->__toString();
      }
      else{
        throw new Exception(
          parent::$error_codes[parent::ERROR_REQUIRED_FIELD] . ' - internal-id',
          parent::ERROR_REQUIRED_FIELD
        );
      }
      

      if (strstr(mb_strtolower($item->category,'UTF-8'), 'квартир') || strstr(mb_strtolower($item->category, 'UTF-8'), 'комнат')) {
        $data['type'] = 'apartament-';
      }
        elseif (strstr(mb_strtolower($item->category, 'UTF-8'), 'дом')) {
        $data['type'] = 'house-';
      }
        elseif (strstr(mb_strtolower($item->category, 'UTF-8'), 'коммерч')) {
        $data['type'] = 'commercial-';
      }
      else {
        throw new Exception(
          parent::$error_codes[parent::ERROR_CATEGORY],
          parent::ERROR_CATEGORY
        );
      }

      if (mb_strstr($item->type, 'продажа')) {
        $data['type'] .= 'sale';
      }
      elseif (mb_strstr($item->type, 'аренда')) {
        $data['type'] .= 'rent';
      }
      else {
        throw new Exception(
          parent::$error_codes[parent::ERROR_TYPE],
          parent::ERROR_TYPE
        );
      }

      if (!($data['region_id'] = self::getRegionId($item->location->region))) {
        throw new Exception(
          parent::$error_codes[parent::ERROR_REGION],
          parent::ERROR_REGION
        );
      }

      if (!empty($data['region_id'])) {
        if (!in_array($data['region_id'], array(77, 78))) {
          if ($item->location->region_node || $item->location->city) {
            if ($item->location->region_node) {
              $nodes = self::prepareRegionNode($item->location);
              $data['address1'] = (string) $item->location->region;
              if (count($nodes) > 0) {
                $data['address1'] .= ', ' . implode(', ', $nodes);
              }

              if ($item->location->city) {
                $data['address1'] .= ', ' . (string) $item->location->city;
              }
            } else {
              $data['address1'] = (string) $item->location->city;
            }
          }
          else {
            throw new Exception(
              parent::$error_codes[parent::ERROR_LOCATION],
              parent::ERROR_LOCATION
            );
          }
        }
        else {
          if ($item->location->region_node || $item->location->metro) {
            $nodes = self::prepareRegionNode($item->location, true);

            $data['address1'] = (string) $item->location->region;
            if (count($nodes) > 0) {
              $data['address1'] .= ', ' . implode(', ', $nodes);
            }
            $data['address1'] = preg_replace('/\,$/', '', trim($data['address1']));
          }
          else {
            throw new Exception(
              parent::$error_codes[parent::ERROR_LOCATION],
              parent::ERROR_LOCATION
            );
          }
        }
      }

      
      if(false !== strpos($data['type'], 'apartament') && empty($item->location->street)) {
        throw new Exception(
          parent::$error_codes[parent::ERROR_ADDRESS],
          parent::ERROR_ADDRESS
        );
      }
      
      $data['address2'] = '';
      if ($item->location->street && $item->location->address->house) {
        $data['address2'] .= $item->location->street;
        if ($item->location->address->house && trim($item->location->address->house) != '') {
          $data['address2'] .=  ', ' . (int) trim($item->location->address->house);
        }
        if ($item->location->address->building && trim($item->location->address->building) != '')
          $data['address2'] .= 'к' . (int) trim($item->location->address->building);
        if ($item->location->address->structure && trim($item->location->address->structure) != '')
          $data['address2'] .= ' стр. ' . (int) trim($item->location->address->structure);
      }

      $data['address_info'] = self::generateAddressInfo($item, $data['region_id']);

      if ($item->latitude && $item->longitude) {
        $data['latitude']  = (float) $item->latitude;
        $data['longitude'] = (float) $item->longitude;
      }
      else {
        $geodata = Geocoder::getCoords($data['address1'] . ', ' . $data['address2']);
        if ($geodata) {
          $data['latitude']  = $geodata['lat'];
          $data['longitude'] = $geodata['lng'];
        } else {
          throw new Exception(
            parent::$error_codes[parent::ERROR_GEODATA],
            parent::ERROR_GEODATA
          );
        }
      }

      $full_price = 'full-price';
      $data['price'] = (int) (!empty($item->$full_price->value)) ? $item->$full_price->value : $item->price->value;

      $currency = (string) (!empty($item->$full_price->currency)) ? $item->$full_price->currency : $item->price->currency;
      $currency = trim($currency);
      $data['price'] = (int)trim($data['price']);

      if (!empty(Currency::$currencies[$currency])) {
        switch ($currency) {
          case 'USD':
          case 'EUR':
            $data['currency'] = $currency;
            $rates = Currency::getRates();
            $data['exchange'] = $rates[$currency]['RUR'];
            break;
          default:
            $data['currency'] = 'RUR';
            $data['exchange'] = 1;
            break;
        }
      }
      else {
        throw new Exception(
          parent::$error_codes[parent::ERROR_CURRENCY],
          parent::ERROR_CURRENCY
        );
      }

      $data['status'] = 'active';

      $created_at = strtotime((string) $item->creation);
      if ($created_at) {
        $data['created_at'] = date('Y-m-d H:i:s', $created_at);
      }
      else {
        $data['created_at'] = date('Y-m-d H:i:s');
      }
      
      //#12221
      //Если есть spec_active_till (окончание периода оплаты), то ставим его
      if(!empty($lot_object['spec_active_till'])){
        $data['active_till'] = $lot_object['spec_active_till'];
      }
      //Если есть "срок годности" у самой записи
      if (!empty($item->expire)) {
        $active_till = $max_active_till = strtotime((string) $item->expire);
        //active_till не может быть больше окончание периода импорта + 3 дня
        if(!empty($data['active_till'])) $max_active_till = strtotime((string) $data['active_till']) + 60 * 60 * 24 * 3;
        if( $active_till > $max_active_till ) //Если "срок годности" слишком большой
          $data['active_till'] = date('Y-m-d H:i:s', $max_active_till); //Ставим дату окончания импорта + 3 дня
        else //Оставляем "родной срок годости"
          $data['active_till'] = date('Y-m-d H:i:s', $active_till);
      }
      else { //Если срок до сих пор не указан, ставим +30 дней с текущей даты
        $data['active_till'] = date(
          'Y-m-d H:i:s',
          time() + 60 * 60 * 24 * 30
        );
      }

      if (!empty($item->agent->phone)) {
        $phone = Toolkit::unformatPhoneNumber($item->agent->phone);

        $data['organization_contact_phone'] = Toolkit::formatPhoneNumber(
          $phone['country'], $phone['area'], $phone['number']
        );
      }

      if (!empty($item->agent->name)) {
        $data['organization_contact_name'] = Toolkit::escape((string) $item->agent->name);
      }
      if (!empty($item->agent->link)) {
        $data['organization_link'] = Toolkit::escape((string) $item->agent->link);
      }
      if (!empty($item->description)) {
        $data['description'] =  Toolkit::escape((string) $item->description);
        // TODO: Remove hardcoded value
        if(mb_strlen($data['description'], 'utf-8') > 1500) {
          throw new Exception(
            parent::$error_codes[parent::ERROR_HUGE_DESC],
            parent::ERROR_HUGE_DESC
          );
        }
      }


      $lot_info = self::prepareAdditionalInfo($data['type'], $item);
      $not_paid = !empty($lot_object['not_paid']) && $lot_object['not_paid'] === true;

      //create Lot
      $lot = self::createLot($data, $lot_info, self::$log->id, $not_paid);

      //save images for lot
      if (count($item->image)) {
        $images = array();
        $images_count = 0;
        foreach ($item->image as $image) {
          if ($images_count == 6) {
            break;
          }
          
          $images[] = trim($image);
          $images_count++;
        }

        if (count($images) > 0) {
          self::loadImages($lot, $images);
        }
      }

      echo "New Lot: " . $lot->id . "\r\n\r\n";
      $lot->free(true);
    }
    catch (Exception $e) {
      self::importErrorLog(
        self::$log,
        self::$internal_id,
        $e->getMessage()
      );
    }
  }

  static protected function prepareAdditionalInfo($type, SimpleXMLElement $item)
  {
    $lot_info = array();
    $formFields = Doctrine::getTable('FormField')
      ->createQuery('f2')
      ->leftJoin('f2.FormField f')
      ->select('f2.*')
      ->addSelect('f.required AS required')
      ->where('f.type = ?', $type)
      ->orderBy('f.position')
      ->execute();

    //additional fields
    $childs = $item->children();
    foreach ($formFields as $ff) {
      $form_field = true;
      foreach ($childs as $name => $val) {
        if ($ff->xml_name == $name) {
          $val = self::prepareFormFieldValue($ff, $val);
          if ($val) {
            $lot_info[$ff->id] = $val;
            $form_field = true;
          }
          elseif ($ff->required == 1) {
            throw new Exception(
              parent::$error_codes[parent::ERROR_REQUIRED_FIELD] . ' - ' . $ff->label,
              parent::ERROR_REQUIRED_FIELD
            );
          }
          break;
        }
        else {
          $form_field = false;
        }
      }

      if (!$form_field && $ff->required == 1) {
        throw new Exception(
          parent::$error_codes[parent::ERROR_REQUIRED_FIELD] . ' - ' . $ff->label,
          parent::ERROR_REQUIRED_FIELD
        );
      }
    }

    return $lot_info;
  }

  /**
   * return array of nodes (region_node, metro)
   * @param SimpleXMLElement $location
   * @param type $is_big_city
   * @return array
   */
  protected static function prepareRegionNode($location = false, $is_big_city = false)
  {
    if ($location instanceof SimpleXMLElement) {
        self::prepareRegionNodes($location);
        if ($is_big_city) {
          self::prepareMetros($location);
        }
    }

    if ($is_big_city) {
      $nodes = array_merge(self::$_region_nodes, self::$_metros);
    }
    else {
      $nodes = self::$_region_nodes;
    }
    return (is_array($nodes)) ? array_unique($nodes) : array();
  }

  protected static function prepareMetros($location = false)
  {
    if ($location) {
      $metro = array();
      if (!empty($location->metro)) {
        foreach ($location->metro as $m) {
          $m = preg_replace('/м\./', '', trim($m));
          $metro[] = 'м. ' . trim($m);
        }
      }
    }

    return self::$_metros = $metro;
  }

  protected static function prepareRegionNodes($location = false)
  {
    if ($location) {
      $region_nodes = array();
      if (!empty($location->region_node)) {
        foreach ($location->region_node as $node) {
          $region_nodes[] = trim($node);
        }
      }
    }

    return self::$_region_nodes = $region_nodes;
  }

  protected static function generateAddressInfo($item, $region_id)
  {
    $region_nodes = array();
    $nodes = self::prepareRegionNode();
    if (count($nodes) > 0) {
      foreach ($nodes as $id => $node) {
        $node = str_replace(Regionnode::$nodot, '', $node);
        $node = str_replace(Regionnode::$socrbefore, '', $node);
        $node = trim($node);
        if ($node != '') {
          $node = Doctrine::getTable('RegionNode')->createQuery()
            ->select('id')
            ->where('name LIKE ?', $node . '%')
            ->andWhere('region_id = ?', $region_id)
            ->limit(1)
            ->fetchOne();
          if ($node){
            $region_nodes[] = $node->id;
          }
        }
      }
    }

    if (!in_array($region_id, array(77, 78))) {
      $city_region = str_replace(Regionnode::$nodot, '', $item->location->city);
      $city_region = str_replace(Regionnode::$socrbefore, '', $city_region);
      $city_region = trim($city_region);
      $city_region = Doctrine::getTable('RegionNode')->createQuery()
        ->select('id')
        ->where('name LIKE ?', $city_region . '%')
        ->andWhere('region_id = ?', $region_id)
        ->limit(1)
        ->fetchOne();
    }
    $data = array(
      'region_node' => $region_nodes,
      'city_region' => (!empty($city_region)) ? $city_region->full_name : trim($item->location->city),
      'street'      => (!empty($item->location->street)) ? trim($item->location->street) : null,
      'address' => array(
        'house'     => (!empty($item->location->address->house)) ? preg_replace('/\D/', '', $item->location->address->house) : '',
        'building'  => (!empty($item->location->address->building)) ? preg_replace('/\D/', '', $item->location->address->building) : '',
        'structure' => (!empty($item->location->address->structure)) ? preg_replace('/\D/', '', $item->location->address->structure) : '',
      )
    );
    return $data;
  }
}
