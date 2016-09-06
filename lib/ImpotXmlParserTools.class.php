<?php

/**
 * Xml parsing tool
 */

class ImpotXmlParserTools {

  protected
  $l = null,
  $user_id = 0;

  public function parseXml($data)
  {
    $data = unserialize($data);

    $this->l = simplexml_load_string($data['xml']);
    $this->user_id = $data['user_id'];

    return $this->$data['xml_type']();
  }

  public function mesto()
  {
    $l = $this->l;
    $user_id = $this->user_id;
    $data = array();


    if (strstr(strtolower($l->category), 'квартир') || strstr(strtolower($l->category), 'комнат'))
      $data['type'] = 'apartament-';
    elseif (strstr(strtolower($l->category), 'дом'))
      $data['type'] = 'house-';
    elseif (strstr(strtolower($l->category), 'коммерч'))
      $data['type'] = 'commercial-';
    else
      return false;

    if (strstr($l->type, 'продажа'))
      $data['type'] .= 'sale';
    elseif (strstr($l->type, 'аренда'))
      $data['type'] .= 'rent';
    else
      return false;

    $data['user_id'] = $user_id;

    if ($l->location->region_node || $l->location->city_region) {
      if ($l->location->region_node) {
        $data['address1'] = (string) $l->location->region_node;
        if ($l->location->city_region)
          $data['address1'] .= ', ' . (string) $l->location->city_region;
      } else {
        $data['address1'] = (string) $l->location->city_region;
      }
    }

    if ($l->location->street && $l->location->address->house) {
      $data['address2'] = $l->location->street . ', ' . (int) $l->location->address->house;
      if ($l->location->address->building)
        $data['address2'] .= 'к' . (int) $l->location->address->building;
      if ($l->location->address->structure)
        $data['address2'] .= ' стр. ' . (int) $l->location->address->structure;
    }

    if (!($data['region_id'] = $this->getRegionId($l->location->region)))
      return false;;

    $data['address_info'] = array(
      'region_node' => (string) $l->location->region_node,
      'city_region' => (string) $l->location->city_region,
      'street' => (string) $l->location->street,
      'address' => array(
        'house' => (string) $l->location->address->house,
        'building' => (string) $l->location->address->building,
        'structure' => (string) $l->location->address->structure
      )
    );

    if ($l->latitude && $l->longitude) {
      $data['latitude'] = (float) $l->latitude;
      $data['longitude'] = (float) $l->longitude;
    }
    else {
      $geodata = Geocoder::getCoords($data['address1'] . ', ' . $data['address2']);
      if ($geodata) {
        $data['latitude'] = $geodata['lat'];
        $data['longitude'] = $geodata['lng'];
      } else
        return false;
    }

    $data['price'] = (int) $l->price->value;
    switch ($l->price->currency) {
      case 'USD':
      case 'EUR':
        $data['currency'] = (string) $l->price->currency;
        $rates = Currency::getRates();
        $data['exchange'] = $rates[(string) $l->price->currency]['RUR'];
        break;
      default:
        $data['currency'] = 'RUR';
        $data['exchange'] = 1;
        break;
    }

    $data['status'] = 'moderate';

    $c_at = strtotime((string) $l->creation);
    if ($c_at)
      $data['created_at'] = date('Y-m-d H:i:s', $c_at);
    else
      $data['created_at'] = date('Y-m-d H:i:s');

    $c_at = strtotime((string) $l->expire);
    if ($c_at)
      $data['active_till'] = date('Y-m-d H:i:s', $c_at);
    else
      $data['active_till'] = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 15);

    $phone = Toolkit::unformatPhoneNumber($l->agent->phone);

    $data['organization_contact_phone'] = Toolkit::formatPhoneNumber($phone['country'], $phone['area'], $phone['number']);

    $data['organization_contact_name'] = Toolkit::escape((string) $l->agent->name);
    $data['description'] = Toolkit::escape((string) $l->description);

    //new lot populate from array $data
    $lot = new Lot(null, true);
    $lot->fromArray($data);
    $lot->save();

    //loading images
    $i_count = 0;

    if (count($l->image)) {
      $images = array();
      $source = sprintf('%s/%s/source', sfConfig::get('sf_web_dir'), sfConfig::get('app_upload_tmp_dir'));

      foreach ($l->image as $image) {
        if ($i_count == 6)
          break;

        $filename = $lot->id . '_' . ($i_count + 1);
        $raw = @file_get_contents((string) $image);
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

          $filename = ($i_count + 1) . $ext;
          if (rename($image, $lot->full_image_path . '/' . $filename)) {
            chmod($lot->full_image_path . '/' . $filename, 0666);
            $images[] = $filename;
            $i_count++;
          }
        }
      }

      if (count($images)) {
        $lot->images = $images;
        $lot->thumb = 1;
      }
    }


    $formFields = Doctrine::getTable('FormField')
        ->createQuery('f2')
        ->leftJoin('f2.FormField f')
        ->where('f.type = ?', $data['type'])
        ->andWhere('f2.xml_name IS NOT NULL')
        ->orderBy('f.position')
        ->execute();

    //additional fields
    $lc = $l->children();
    foreach ($lc as $name => $val) {
      foreach ($formFields as $ff) {
        if ($ff->xml_name == $name) {
          switch ($ff->type) {
            case 'float':
              $val = (float) $val;
              break;
            case 'integer':
              $val = (int) $val;
              break;
            case 'year':
              $val = ((int) $val > 1861) ? (int) $val : 0;
              break;
            case 'select':
              $val = trim((string) $val);
              $val = in_array($val, explode("\n", $ff['value'])) ? $val : 0;
              break;
            case 'price':
              $value = (int) $val->value;
              $currency = trim((string) $val->currency);
              $currency = in_array($currency, array('RUR', 'USD', 'EUR')) ? $currency : 0;
              $val = ($value && $currency) ? $currency . $value : 0;
              break;
            case 'radio':
              $val = trim((string) $val);
              $val = in_array($val, explode("\n", $ff['value'])) ? $val : 0;
              break;
            case 'radiocombo':
              $val = trim((string) $val);
              break;
            case 'multiple':
              $values = explode(',', $val);
              $allowed = explode("\n", $ff['value']);
              foreach ($values as $key => $value)
                $values[$key] = in_array(trim($value), $allowed) ? trim($value) : null;
              $val = !empty($values) ? implode(', ', $values) : 0;
              break;
          }
          if ($val)
            $this->addLotInfo($ff->id, $val, $lot->id);
        }
      }
    }


    $brief = array('type' => $data['type']);
    foreach ($this->params as $id => $param) {
      $brief["field$id"] = $param;
    }
    $lot->brief = @DynamicForm::makeBrief($brief);

    $lot->save();

    return $lot->id;
  }

  public function gde() {

  }

  public function irr() {

  }

  public function mail() {

  }

  public function winner() {

  }

  public function mir() {

  }

  public function dmir() {

  }

  protected function addLotInfo($field_id, $val, $lot_id)
  {
    $li = new LotInfo(NULL, true);
    $li->lot_id = $lot_id;
    $li->field_id = $field_id;
    $this->params[$field_id] = (string) $val;

    $li->value = (string) $val;
    $li->save();
    $li = null;
  }

  protected function getRegionId($r_name)
  {
    $r_name = str_replace(array(
        ' область',
        ' обл.',
        ' край',
        ' АО',
        'г. ',
        'г.'
        ), array(
        '',
        '',
        '',
        '',
        '',
        ''
        ), $r_name);


    $q = Doctrine_Query::create()
        ->select('r.id')
        ->from('Region r')
        ->where('r.name LIKE ?', '%' . trim($r_name) . '%')
        ->fetchOne();

    return ($q) ? $q->id : false;
  }

}