<?php
/**
 * Class for import CSV files
 * @package    domus
 * @author     <s1l3nt@garin-studio.ru> Garin Studio
 */
class ImportCSV extends ImportFile
{
  protected
    $lots_counter = 0,
    $internal_id  = null;

  /**
   * Import files from format "Mesto.ru"
   * @return bool
   */
  protected function mesto()
  {
    try
    {
      $fhandle = fopen($this->_file_path, 'r');
      if (is_resource($fhandle)) {
        fgets($fhandle); //ignore first line
        while(!feof($fhandle)) {
          try {
            $this->_lot = array();
            $line = fgets($fhandle);
            if ($line) {
              $this->lots_counter++;
              $lot = explode(';', $line);

              if (!empty($lot[0])) {
                $this->internal_id = $lot[0];
              }
              else {
                $this->internal_id = null;
              }
              //check lot type
              if (!empty($lot[1])) {
                $category = str_replace('"', '', (string) $lot[1]);
                $this->_lot['type'] = Lot::$categories[$category];
                if (!empty($lot[2])) {
                  $type = str_replace('"', '', (string) $lot[2]);
                  $this->_lot['type'] .= '-' . Lot::$type[$type];
                }
                else {
                  throw new Exception(
                    parent::$error_codes[parent::ERROR_TYPE],
                    parent::ERROR_TYPE
                  );
                }
              }
              else {
                throw new Exception(
                  parent::$error_codes[parent::ERROR_CATEGORY],
                  parent::ERROR_CATEGORY
                );
              }

              if (empty(Lot::$types[$this->_lot['type']])) {
                throw new Exception(
                  parent::$error_codes[parent::ERROR_TYPE],
                  parent::ERROR_TYPE
                );
              }

              //import base fields && additional info
              $this->importBaseFields($lot);
              $this->prepareMestoAdditionalInfo($lot);
              $lot_info = $this->_lot['additional_info'];
              unset($this->_lot['additional_info']);
              $local_lot = $this->createLot($this->_lot, $lot_info);
              self::loadImages($local_lot, $lot);
            }
          }

          catch (Exception $e)
          {
            $this->importErrorLog(
              $this->log,
              $this->internal_id,
              $e->getMessage()
            );
          }
        }
      }
      else {
        throw new Exception(
          parent::$error_codes[parent::ERROR_READING_FILE],
          parent::ERROR_READING_FILE
        );
      }
    }

    catch (Exception $e)
    {
      $this->importErrorLog(
        $this->log,
        null,
        $e->getMessage()
      );
    }

    return $this->lots_counter;
  }

  protected function importBaseFields($lot)
  {
    list(
      $internal_id, $category, $type,
      $region, $address1, $address2,
      $coords, $created_at, $active_till,
      $description, $rieltor_info, $images
    ) = $lot;

    $this->_lot['user_id'] = $this->_user_id;
    $this->_lot['status'] = 'active';
    if (!empty($region)) {
      $region = str_replace('"', '', (string) $region);
      $this->_lot['region_id'] = $this->getRegionId($region);
      if (!$this->_lot['region_id']) {
        throw new Exception(
          parent::$error_codes[parent::ERROR_REGION],
          parent::ERROR_REGION
        );
      }
    }
    else {
      throw new Exception(
        parent::$error_codes[parent::ERROR_REGION],
        parent::ERROR_REGION
      );
    }
    //Address1
    if (!empty($address1)) {
      $this->_lot['address1'] = $region . ', ' . str_replace('"', '', (string) $address1);
    }
    else {
      throw new Exception(
        parent::$error_codes[parent::ERROR_LOCATION],
        parent::ERROR_LOCATION
      );
    }

    //Address2
    if (!empty($address2)) {
      $this->_lot['address2'] = str_replace('"', '', (string) $address2);
    }
    else {
      throw new Exception(
        parent::$error_codes[parent::ERROR_ADDRESS],
        parent::ERROR_ADDRESS
      );
    }

    //Coords
    if (!empty($coords)) {
      $coords = explode(',', str_replace('"', '', $coords));
      $this->_lot['latitude'] = (string) trim($coords[0]);
      $this->_lot['longitude'] = (string) trim($coords[1]);
    }
    else {
      $geodata = Geocoder::getCoords($this->_lot['address1'] . ', ' . $this->_lot['address2']);
      if ($geodata) {
        $this->_lot['latitude']  = $geodata['lat'];
        $this->_lot['longitude'] = $geodata['lng'];
      } else {
        throw new Exception(
          parent::$error_codes[parent::ERROR_GEODATA],
          parent::ERROR_GEODATA
        );
      }
    }

    //generate address_info
    $this->generateAddressInfo();

    //Created at
    if (!empty($created_at)) {
      $created_at = strtotime(trim(str_replace('"', '', (string) $created_at)));
      if ($created_at) {
        $this->_lot['created_at'] = date('Y-m-d H:i:s', $created_at);
      }
      else {
        $this->_lot['created_at'] = date('Y-m-d H:i:s');
      }
    }
    else {
      $this->_lot['created_at'] = date('Y-m-d H:i:s');
    }

    //Active_till
    if (!empty($active_till)) {
      $active_till = strtotime(trim(str_replace('"', '', (string) $active_till)));
      if ($active_till) {
        $this->_lot['active_till'] = date('Y-m-d H:i:s', $active_till);
      }
      else {
        $this->_lot['active_till'] = date(
          'Y-m-d H:i:s',
          time() + 60 * 60 * 24 * 30
        );
      }
    }
    else {
      $this->_lot['active_till'] = date(
        'Y-m-d H:i:s',
        time() + 60 * 60 * 24 * 30
      );
    }

    //Description
    if (!empty($description)) {
      $description = trim(str_replace('"', '', (string) $description));
      if (mb_strlen($description) > 1) {
        $this->_lot['description'] = $description;
      }
    }
    //Additional rieltor info
    if (!empty($rieltor_info)) {
      $org_info = explode(',', trim(str_replace('"', '', (string) $rieltor_info)));
      if (!empty($org_info[0])) {
        $phone = Toolkit::unformatPhoneNumber(trim((string) $org_info[0]));

        $this->_lot['organization_contact_phone'] = Toolkit::formatPhoneNumber(
          $phone['country'], $phone['area'], $phone['number']
        );
      }
      if (!empty($org_info[1])) {
        $this->_lot['organization_contact_name'] = Toolkit::escape(trim((string) $org_info[1]));
      }
      if (!empty($org_info[2])) {
        $this->_lot['organization_link'] = Toolkit::escape(trim((string) $org_info[2]));
      }
      unset($org_info);
    }
  }

  protected function prepareMestoAdditionalInfo($lot)
  {
    switch($this->_lot['type']) {
      case 'apartament-sale':
        $this->_lot['additional_info'] = array();
        @list(
          $internal_id, $category, $type,
          $region, $address1, $address2,
          $coords, $created_at, $active_till,
          $description, $rieltor_info, $images, $this->_lot['additional_info'][2],
          $this->_lot['additional_info'][54], $this->_lot['additional_info'][1],
          $this->_lot['additional_info'][3], $this->_lot['additional_info'][4],
          $this->_lot['additional_info'][5], $this->_lot['additional_info'][6],
          $this->_lot['additional_info'][7], $this->_lot['additional_info'][8],
          $this->_lot['additional_info'][9], $this->_lot['additional_info'][10],
          $this->_lot['additional_info'][11], $this->_lot['additional_info'][12],
          $this->_lot['additional_info'][13], $this->_lot['additional_info'][14],
          $this->_lot['additional_info'][15], $this->_lot['additional_info'][20],
        ) = $lot;
        $price = $this->_lot['additional_info'][2];
        break;
      case 'apartament-rent':
        @list(
          $internal_id, $category, $type,
          $region, $address1, $address2,
          $coords, $created_at, $active_till,
          $description, $rieltor_info, $images, $this->_lot['additional_info'][16],
          $this->_lot['additional_info'][68],
          $this->_lot['additional_info'][55], $this->_lot['additional_info'][1],
          $this->_lot['additional_info'][3], $this->_lot['additional_info'][4],
          $this->_lot['additional_info'][17], $this->_lot['additional_info'][18],
          $this->_lot['additional_info'][19], $this->_lot['additional_info'][66],
          $this->_lot['additional_info'][5], $this->_lot['additional_info'][6],
          $this->_lot['additional_info'][7], $this->_lot['additional_info'][8],
          $this->_lot['additional_info'][9], $this->_lot['additional_info'][10],
          $this->_lot['additional_info'][11], $this->_lot['additional_info'][15],
          $this->_lot['additional_info'][12], $this->_lot['additional_info'][21]
        ) = $lot;
        $price = $this->_lot['additional_info'][16];
        break;
      case 'house-sale':
        @list(
          $internal_id, $category, $type,
          $region, $address1, $address2,
          $coords, $created_at, $active_till,
          $description, $rieltor_info, $images, $this->_lot['additional_info'][2],
          $this->_lot['additional_info'][64],
          $this->_lot['additional_info'][26], $this->_lot['additional_info'][27],
          $this->_lot['additional_info'][30], $this->_lot['additional_info'][31],
          $this->_lot['additional_info'][32], $this->_lot['additional_info'][33],
          $this->_lot['additional_info'][34], $this->_lot['additional_info'][5],
          $this->_lot['additional_info'][28], $this->_lot['additional_info'][29],
          $this->_lot['additional_info'][35], $this->_lot['additional_info'][4],
          $this->_lot['additional_info'][36], $this->_lot['additional_info'][37],
          $this->_lot['additional_info'][38], $this->_lot['additional_info'][39],
          $this->_lot['additional_info'][40], $this->_lot['additional_info'][41],
          $this->_lot['additional_info'][42], $this->_lot['additional_info'][67],
          $this->_lot['additional_info'][43], $this->_lot['additional_info'][44],
          $this->_lot['additional_info'][22]
        ) = $lot;
        $price = $this->_lot['additional_info'][2];
        break;
      case 'house-rent':
        @list(
          $internal_id, $category, $type,
          $region, $address1, $address2,
          $coords, $created_at, $active_till,
          $description, $rieltor_info, $images, $this->_lot['additional_info'][16],
          $this->_lot['additional_info'][26], $this->_lot['additional_info'][27],
          $this->_lot['additional_info'][5], $this->_lot['additional_info'][28],
          $this->_lot['additional_info'][61], $this->_lot['additional_info'][60],
          $this->_lot['additional_info'][56], $this->_lot['additional_info'][57],
          $this->_lot['additional_info'][58], $this->_lot['additional_info'][59],
          $this->_lot['additional_info'][18], $this->_lot['additional_info'][19],
          $this->_lot['additional_info'][35], $this->_lot['additional_info'][4],
          $this->_lot['additional_info'][36], $this->_lot['additional_info'][37],
          $this->_lot['additional_info'][43], $this->_lot['additional_info'][44],
          $this->_lot['additional_info'][23]
        ) = $lot;
        $price = $this->_lot['additional_info'][16];
        break;
      case 'commercial-sale':
        @list(
          $internal_id, $category, $type,
          $region, $address1, $address2,
          $coords, $created_at, $active_till,
          $description, $rieltor_info, $images, $this->_lot['additional_info'][2],
          $this->_lot['additional_info'][3], $this->_lot['additional_info'][4],
          $this->_lot['additional_info'][5], $this->_lot['additional_info'][24],
          $this->_lot['additional_info'][45], $this->_lot['additional_info'][46],
          $this->_lot['additional_info'][47], $this->_lot['additional_info'][48],
          $this->_lot['additional_info'][49], $this->_lot['additional_info'][50],
          $this->_lot['additional_info'][51], $this->_lot['additional_info'][52]
        ) = $lot;
        $price = $this->_lot['additional_info'][2];
        break;
      case 'commercial-rent':
        @list(
          $internal_id, $category, $type,
          $region, $address1, $address2,
          $coords, $created_at, $active_till,
          $description, $rieltor_info, $images, $this->_lot['additional_info'][53],
          $this->_lot['additional_info'][69], $this->_lot['additional_info'][3],
          $this->_lot['additional_info'][4], $this->_lot['additional_info'][5],
          $this->_lot['additional_info'][25], $this->_lot['additional_info'][45],
          $this->_lot['additional_info'][46], $this->_lot['additional_info'][48],
          $this->_lot['additional_info'][49], $this->_lot['additional_info'][50],
          $this->_lot['additional_info'][52], $this->_lot['additional_info'][63]
        ) = $lot;
        $price = $this->_lot['additional_info'][53];
        break;
    }

    //Price
    if (!empty($price)) {
      $price = explode(',', str_replace('"', '', $price));
      if (count($price) == 2) {
        $this->_lot['price'] = (int) trim($price[0]);
        $currency = (string) trim($price[1]);
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
        $this->_lot['currency'] = (string) trim($price[1]);
      }
      else {
        throw new Exception(
          parent::$error_codes[parent::ERROR_PRICE],
          parent::ERROR_PRICE
        );
      }
    }

    $formFields = Doctrine::getTable('FormField')
      ->createQuery('f2')
      ->leftJoin('f2.FormField f')
      ->select('f2.*')
      ->addSelect('f.required AS required')
      ->where('f.type = ?', $this->_lot['type'])
      ->orderBy('f.position')
      ->execute();

    foreach ($formFields as $ff) {
      if (!empty($this->_lot['additional_info'][$ff->id])) {
        $this->_lot['additional_info'][$ff->id] = $this->prepareFormFieldValue(
          $ff,
          trim(str_replace('"', '', (string) $this->_lot['additional_info'][$ff->id]))
        );
      }
      else if ($ff->required) {
        throw new Exception(
          parent::$error_codes[parent::ERROR_REQUIRED_FIELD] . ' - ' . $ff->label,
          parent::ERROR_REQUIRED_FIELD
        );
      }
    }
  }

  protected static function loadImages(Lot $lot, $lot_data = array())
  {
    if (!empty($lot_data[11])) {
      $images = explode(',', str_replace('"', '', $lot_data[11]));
      parent::loadImages($lot, $images);
    }
  }

  protected function generateAddressInfo()
  {
    @list($region_node, $city_region_orig) = explode(',', $this->_lot['address1']);
    $region_node = str_replace(Regionnode::$nodot, '', $region_node);
    $region_node = str_replace(Regionnode::$socrbefore, '', $region_node);
    $region_node = trim($region_node);
    $region_node = Doctrine::getTable('RegionNode')->createQuery()
      ->select('id')
      ->where('name LIKE ?', $region_node . '%')
      ->andWhere('region_id = ?', $this->_lot['region_id'])
      ->fetchOne();

    if (!in_array($this->_lot['region_id'], array(77, 78))) {
      $city_region = str_replace(Regionnode::$nodot, '', $city_region_orig);
      $city_region = str_replace(Regionnode::$socrbefore, '', $city_region);
      $city_region = trim($city_region);
      $city_region = Doctrine::getTable('RegionNode')->createQuery()
        ->select('id')
        ->where('name LIKE ?', $city_region . '%')
        ->andWhere('region_id = ?', $this->_lot['region_id'])
        ->fetchOne();
    }
    @list($street, $house, $building, $structure) = explode(',', $this->_lot['address2']);

    $this->_lot['address_info'] = array(
      'region_node' => (!empty($region_node)) ? $region_node->id : null,
      'city_region' => (!empty($city_region)) ? $city_region->id : trim($city_region_orig),
      'street'      => (!empty($street)) ? trim($street) : null,
      'address' => array(
        'house'     => (!empty($house)) ? preg_replace('/\D/', '', $house) : null,
        'building'  => (!empty($building)) ? preg_replace('/\D/', '', $building) : null,
        'structure' => (!empty($structure)) ? preg_replace('/\D/', '', $structure) : null,
      )
    );
  }
}