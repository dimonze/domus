<?php

class ExportLotToYandexWorker extends sfGearmanWorker
{
  public
    $name = 'export-lot-to-yandex',
    $methods = array('export_lot_to_yandex');
  private
    $options,
    $document,
    $root;

  protected function configure()
  {
    ini_set('memory_limit', '50M');
    $this->_configuration->loadHelpers('Domus');
  }

  public function doExportLotToYandex(GearmanJob $job)
  {
    $this->startJob();
    $this->options = unserialize($job->workload());

    try {
      if (!($lot = Doctrine::getTable('Lot')->find($this->options['id']))) {
        throw new ExportLotToYandexSkipException('not found');
      }
      if (!$this->checkRules($lot)) {
        throw new ExportLotToYandexSkipException('did not pass rules');
      }
      !$this->checkPhones($lot);
      
      if ('new_building-sale' == $lot->type) {
        throw new ExportLotToYandexSkipException('new_building-sale is not available yet');
      }

      $this->document = new DOMDocument('1.0', 'UTF-8');
      $this->document->formatOutput = true;
      $this->root = $this->append('offer', null, null, $this->document);
      $this->root->setAttribute('internal-id', $lot->id);

      $this->appendBase($lot);
      $this->appendType($lot);
      $this->appendLocation($lot);
      $this->appendInfo($lot);
      $this->appendImages($lot);
      $this->appendContact($lot);


      $file_name = sfConfig::get('sf_data_dir') . '/' . $this->options['file_name'] . '.yrl';
      $fh = fopen($file_name, 'a');
      flock($fh, LOCK_EX);
      fwrite($fh, $this->document->saveXML($this->root));
      fclose($fh);

      $result = 'complete';
    }
    catch (ExportLotToYandexSkipException $e) {
      $result = 'error: ' . $e->getMessage();
    }

    gc_collect_cycles();
    return $this->completeJob($job, sprintf('id=%-8d %s', $lot->id, $result));
  }


  private function checkRules(Lot $lot)
  {
    switch ($lot->type) {
      case 'apartament-sale':
        return
          $lot->price_exchanged > (77 == $lot->region_id ? 2500000 : 1500000) &&
          $lot->getLotInfoField(1) > 10;

      case 'apartament-rent':
        return
          $lot->price_exchanged > (77 == $lot->region_id ? 10000 : 3000) &&
          $lot->getLotInfoField(1) > 10;

      case 'house-sale':
        return
          $lot->price_exchanged > (77 == $lot->region_id ? 300000 : 200000) &&
          ($lot->getLotInfoField(26) >= 10 || $lot->getLotInfoField(27) >= 2);

      case 'house-rent':
        return
          $lot->price_exchanged > (77 == $lot->region_id ? 10000 : 3000) &&
          ($lot->getLotInfoField(26) >= 10 || $lot->getLotInfoField(27));

      case 'commercial-sale':
      case 'commercial-rent':
        return $lot->getLotInfoField(46) > 10;
    }
  }

  private function checkStreetAddress($string, $is_city)
  {
    $string = trim($string);

    if (!$string && $is_city) {
      throw new ExportLotToYandexSkipException('street: is empty while location is city');
    }
    elseif (!$string) {
      // nothing to check
      return;
    }

    if (!strpos($string, ' ')) {
      throw new ExportLotToYandexSkipException('street: no spaces');
    }
    if (mb_strlen($string) <= 8) {
      throw new ExportLotToYandexSkipException('street: too short');
    }

    if (preg_match('/^(д|дер|п|пос|г|р|р-н)(\.|\s)/i', $string)) {
      throw new ExportLotToYandexSkipException('street: bad abbreviations found');
    }

    // some stupid exceptions
    $exceptions = array('/без.*улицы/i', '/поселок|деревня|водохр/i', '/^в\s+.+/i', '/^[,.]/', '/[^а-я0-9.,\s-\/]+/iu');
    foreach ($exceptions as $regex) {
      if (preg_match($regex, $string)) {
        throw new ExportLotToYandexSkipException('street: another stupidity pattern');
      }
    }
  }

  private function checkPhones(Lot $lot)
  {
    foreach (explode(',', $lot->organization_contact_phone) as $phone) {
      if (trim($phone) && !Toolkit::unformatPhoneNumber($phone)) {
        throw new ExportLotToYandexSkipException('bad phone: ' . $phone);
      }
    }
  }

  private function appendBase(Lot $lot)
  {
    $this->append('url', Toolkit::getLotUrlForWorkers($this->options['host'], $lot));
    $this->append('description', $lot->full_description, 'text');

    //Подставляем рандомное время обновления объявления на сайте
    $created_at = strtotime($lot->active_till) - rand(20, 60) * 24 * 3600;
    $updated_at = strtotime($lot->updated_at);

    if ($created_at > strtotime(date('Y-m-d H:i:s'))) {
      $created_at = strtotime(date('Y-m-d H:i:s'));
      $updated_at = $created_at;
    }

    if (null != $updated_at && $created_at > $updated_at) {
      $upd = $updated_at;
      $updated_at = $created_at;
      $created_at = $upd;
    }
    else if (null === $updated_at){
      $updated_at = $created_at;
    }

    $this->append('creation-date', $created_at, 'date');
    $this->append('last-update-date', $updated_at, 'date');
    $this->append('expire-date', $lot->active_till, 'date');
  }

  private function appendType(Lot $lot)
  {
    list($prop_type, $offer_type) = explode('-', $lot->type);

    $this->append('type', 'sale' == $offer_type ? 'продажа' : 'аренда');
    $this->append('property-type', 'commercial' == $prop_type ? 'коммерческая' : 'жилая');

    switch ($lot->type) {
      case 'apartament-sale':
        $category = 'комната' == $lot->getLotInfoField(54) ? 'комната' : 'квартира';
        break;

      case 'apartament-rent':
        $category = 'комната' == $lot->getLotInfoField(55) ? 'комната' : 'квартира';
        $period = $lot->getLotInfoField(68);
        break;

      case 'house-sale':
        $category = $lot->getLotInfoField(64);
        if (null !== $category) {
          if (strpos($category, 'дома')) {
            $category = 'часть дома';
          }
          elseif (false !== strpos($category, 'дом')) {
            $category = 'дом';
          }
          elseif (false !== strpos($category, 'участок')) {
            $category = 'участок';
          }
          else {
            $category = 'дом';
          }
        }else {
          $category = 'дом';
        }
        break;

      case 'house-rent':
        $category = 'дом';
        $period = 'месяц';
        break;

      case 'commercial-sale':
        $category = 'дом';
        break;

      case 'commercial-sale':
        $category = 'дом';
        $period = $lot->getLotInfoField(69);
        break;
    }

    $this->append('category', $category);

    $price = $this->append('price');
    $this->append('value', $lot->price, null, $price);
    $this->append('currency', $lot->currency, null, $price);
    if (!empty($period)) {
      $this->append('period', $period, null, $price);
    }
  }

  private function appendLocation(Lot $lot)
  {
    $location = $this->append('location');

    $this->append('country', 'Россия', null, $location);
    $city_in_nodes = false;
    foreach($lot->getRegionnode(true) as $node) {
      if($node->socr == 'г') {
        $city_in_nodes = $node;
      }
      if($node->is_metro) {
        $metro = $this->append('metro', null, null, $location);
        $this->append('name', $node->name, null, $metro);
      }
    }

    if ($city_in_nodes && in_array($lot->region_id, array(77, 78))) {
      $is_city = true;
      $this->append('region', $lot->Region->name, null, $location);
      $this->append('locality-name', $city_in_nodes->full_name, null, $location);
    }
    else if (in_array($lot->region_id, array(77, 78))) {
      $is_city = true;
      $this->append('locality-name', $lot->Region->name, null, $location);
    }
    else {
      $this->append('region', $lot->Region->name, null, $location);

      if (!($region_node = $lot->getRegionnode())) {
        throw new ExportLotToYandexSkipException('region_node is undefined');
      }

      // has parent
      if ($region_node->parent) {
        $is_city = 'г' == $region_node->socr;
        $this->append('district', $region_node->Regionnode->full_name, null, $location);
        $this->append('locality-name', $region_node->full_name, null, $location);
      }
      // is parent
      elseif ($region_node->has_children) {
        if (preg_match('/(р|р-н|айон|обл|область)(\.|\s)/i', $lot->address_info['city_region'])) {
          throw new ExportLotToYandexSkipException('locality-name: includes wrong abbr');
        }

        $this->append('district', $region_node->full_name, null, $location);
        $this->append('locality-name', $lot->address_info['city_region'], null, $location);
      }
      // state city
      else {
        $is_city = 'г' == $region_node->socr;
        $this->append('locality-name', $region_node->full_name, null, $location);
      }
    }

    $this->checkStreetAddress($lot->address2, !empty($is_city));
    if ($address = $this->prepareAddress2($lot)) {
      $this->append('address', $address, null, $location);
    }
  }

  private function appendInfo(Lot $lot)
  {
    $map = array(
      1  => array('name' => 'area', 'type' => 'unit'),
      3  => array('name' => 'floor', 'notempty' => true),
      4  => array('name' => 'floors-total', 'notempty' => true),
      5  => array('name' => 'built-year', 'notempty' => true),
      6  => array('name' => 'building-type'),
      8  => array('name' => 'kitchen-space', 'type' => 'unit'),
      11 => array('name' => 'bathroom-unit'),
      14 => array('name' => 'renovation'),
      15 => array('name' => 'balcony'),
      18 => array('name' => 'room-furniture', 'type' => 'boolean'),
      19 => array('name' => array('washing-machine', 'refrigerator')),
      26 => array('name' => 'area', 'type' => 'unit'),
      27 => array('name' => 'lot-area', 'type' => 'unit'),
      28 => array('name' => 'building-type'),
      30 => array('name' => 'heating-supply', 'type' => 'boolean'),
      31 => array('name' => 'gas-supply', 'type' => 'boolean'),
      32 => array('name' => 'electricity-supply', 'type' => 'boolean'),
      33 => array('name' => 'water-supply', 'type' => 'boolean'),
      34 => array('name' => 'sewerage-supply', 'type' => 'boolean'),
      35 => array('name' => array('rooms', 'rooms-offered')),
      54 => array('name' => array('rooms', 'rooms-offered')),
      55 => array('name' => array('rooms', 'rooms-offered')),
      56 => array('name' => 'gas-supply', 'type' => 'boolean'),
      57 => array('name' => 'electricity-supply', 'type' => 'boolean'),
      58 => array('name' => 'water-supply', 'type' => 'boolean'),
      59 => array('name' => 'sewerage-supply', 'type' => 'boolean'),
      60 => array('name' => 'heating-supply', 'type' => 'boolean'),
      61 => array('name' => 'renovation'),
    );

    $info = $lot->LotInfoArrayNoGroups;
    foreach ($map as $key => $params) {
      if (empty($info[$key])) {
        continue;
      }

      $value = $info[$key];

      if (!isset($params['type']) || 'unit' != $params['type']) {
        $value = $value['value'];
      }
      if (!empty($params['notempty']) && !$value) {
        continue;
      }

      if (in_array('rooms', (array) $params['name'])) {
        $value = 'комната' == $value ? 1 : (int) mb_substr($value, 0, 1);
        if ($value <= 0) {
          throw new ExportLotToYandexSkipException('no rooms defined');
        }
      }

      if ('built-year' == $params['name'] && !empty($value)) {
        if ($value < 1861 || $value > (date('Y') + 10)) {
          continue;
        }
      }
      if ('area' == $params['name'] && isset($info[64]) && 'участок' == $info[64]) {
        continue;
      }

      foreach ((array) $params['name'] as $name) {
        $this->append($name, $value, isset($params['type']) ? $params['type'] : null);
      }
    }
  }

  private function appendImages(Lot $lot)
  {
    foreach (lot_images($lot) as $image) {
      $this->append('image', $image['big']);
    }
  }

  private function appendContact(Lot $lot)
  {
    $user = $lot->User;

    $phones = array();
    foreach (explode(',', $lot->organization_contact_phone) as $phone) {
      if ($phone = trim($phone)) {
        $phones[] = $phone;
      }
    }

    $category = 'агентство';

    if (!in_array($user->email, $this->options['sources'])) {
      $name = $user->name;

      if ('owner' == $user->type) {
        $category = 'владелец';
      }

      if ($user->is_inner || $user->is_partner || 22835 == $user->id) {
        // согласно требованиям Яндекса
        if ($lot->organization_contact_name) {
          $name = $lot->organization_contact_name;
        }
        if ($user->is_partner) {
          $partner = $organization = $user->company_name;
        }

        if (!count($phones)) {
          $phones[] = $user->phone;
        }
      }
      else {
        $phones[] = $user->phone;
        if ($user->company_name) {
          $organization = $user->company_name;
          if ($lot->organization_contact_name) {
            $organization .= ' - ' . $lot->organization_contact_name;
          }
        }
      }
    }
    else {
      $name = 'mesto.ru';
    }

    $node = $this->append('sales-agent');
    $this->append('name', $name, null, $node);
    $this->append('category', $category, null, $node);

    if (isset($partner)) {
      $this->append('partner', $partner, null, $node);
    }
    if (isset($organization)) {
      $this->append('organization', $organization, null, $node);
    }

    if (!count($phones)) {
      throw new ExportLotToYandexSkipException('no phone');
    }
    foreach ($phones as $phone) {
      $this->append('phone', $phone, null, $node);
    }
  }

  /**
   * @param string $tag_name
   * @param mixed $value
   * @param string $value_type
   * @param DOMNode $node
   * @return DOMElement
   */
  private function append($tag_name, $value = null, $value_type = null, DOMNode $node = null)
  {
    if (!$node) {
      $node = $this->root;
    }

    $element = $this->document->createElement($tag_name);

    if ($value) {
      switch ($value_type) {
        case 'text':
          $cdata = true;
          // strip chars below 0x20
          $value = preg_replace('/[\x0-\x1f]+/', '', strip_tags(nl2br($value)));
          break;

        case 'date':
          $value = date('c', is_numeric($value) ? $value : strtotime($value));
          break;

        case 'bool':
        case 'boolean':
          $value = (int) !in_array($value, array('нет', 'перспектива'));
          break;

        case 'unit':
          $childs = array();
          $childs[0] = $this->document->createElement('value');
          $childs[0]->nodeValue = trim(preg_replace('/[^0-9.,]/', '', $value['value']));
          $childs[1] = $this->document->createElement('unit');
          $childs[1]->nodeValue = trim(preg_replace('/\.$/', '', trim($value['help'])));
          break;
      }

      if (!empty($cdata)) {
        $element->appendChild($this->document->createCDATASection($value));
      }
      elseif (!empty($childs)) {
        foreach ($childs as $child) {
          $element->appendChild($child);
        }
      }
      else {
        $element->nodeValue = $value;
      }
    }

    $node->appendChild($element);
    return $element;
  }

  private function prepareAddress2(Lot $lot) {
    $info = $lot->address_info;
    $bad = "#^(|[XxХх0\s]+)$#u";
    if(preg_match($bad, $info['address']['house'])
    && preg_match($bad, $info['address']['building'])
    && preg_match($bad, $info['address']['structure'])) {
      $address = $info['street'];
    }
    else {
      $address = $lot->address2;
    }
    return trim($address);
  }
}

class ExportLotToYandexSkipException extends Exception
{ }
