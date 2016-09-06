<?php

abstract class Parser
{
  protected
    $regions = array(),
    $region_id, $type, $fetcher,
    $user_id;

  public function __construct($type, $region_id, $fetcher_options = array())
  {
    if (!$this->user_id) {
      throw new Exception('$this->user_id must me set!');
    }

    $this->type = $type;
    $this->region_id = $region_id;
    $this->fetcher = new Fetcher($fetcher_options);
    sfContext::getInstance()->getConfiguration()->loadHelpers('Word');

    // Init regions
    $stmt = Doctrine::getTable('Region')->getConnection()->prepare('
      select id, name from region
    ');
    $stmt->execute(array());
    while ($row = $stmt->fetch(Doctrine::FETCH_ASSOC)) {
      $this->regions[$row['id']] = $row['name'];
    }
    $stmt->closeCursor();
  }

  /**
   * Check data for completeness
   * @param array $data
   * @return boolean
   */
  public function isEnoughData(array $data)
  {
    if (empty($data['url']) || empty($data['address1']) || empty($data['price'])) {
      return false;
    }
    if ($data['is_city'] && empty($data['address2'])) {
      return false;
    }

    switch ($this->type) {
      case 'apartament-sale':
        return !empty($data['field54']) && (isset($data['field1']) && $data['field1'] > 8);

      case 'apartament-rent':
        return !empty($data['field55']) && (isset($data['field1']) && $data['field1'] > 8);

      case 'house-sale':
      case 'house-rent':
        return !empty($data['field26']) || !empty($data['field27']);
    }
  }

  /**
   * Check data for uniqueness
   * @param array $data
   * @return boolean
   */
  public function isUnique(array $data)
  {
    $conn = Doctrine::getTable('Lot')->getConnection();

    $stmt = $conn->prepare('select id from lot where organization_link = ?');
    $stmt->execute(array($data['url']));
    $exists = $stmt->fetch(Doctrine::FETCH_COLUMN);
    $stmt->closeCursor();
    if ($exists) {
      return false;
    }

    return true;
  }

  /**
   * Import Lot from data
   * @param array $data
   * @return integer|false
   */
  public function import(array $data)
  {
    if (empty($data['url']) || empty($data['address1']) || empty($data['price'])) {
      return false;
    }

    //helper for create address_info array
    $helper = new AddressHelper();
    $address_info = $helper->parseAddress($data['address1'] . ', ' . $data['address2']);

    $data = array_merge($data, array(
      'user_id'                    => $this->user_id,
      'type'                       => $this->type,
      'region_id'                  => $this->region_id,
      'organization_link'          => $data['url'],
      'organization_contact_phone' => empty($data['phones']) ? '' : implode(', ', $data['phones']),
      'currency'                   => empty($data['currency']) ? 'RUR' : $data['currency'],
      'editable'                   => false,
      'created_at'                 => empty($data['created_at']) ? date('Y-m-d H:i:s') : $data['created_at'],
      'parsed_at'                  => date('Y-m-d H:i:s'),
      'active_till'                => date('Y-m-d H:i:s', strtotime('+30 days')),
      'address_info'               => $address_info,
      'LotInfo'                    => array(),
    ));

    if ('apartament-rent' == $this->type && empty($data['field68'])) {
      $data['field68'] = 'месяц';
    }

    $images = isset($data['images']) ? $data['images'] : array();
    unset($data['images']);

    $lot = new Lot();
    $lot->fromArray($data, true);
    $lot->brief = DynamicForm::makeBrief($data);

    if (!$lot->trySave()) {
      return false;
    }

    // load fields
    foreach ($data as $key => $value) {
      if (preg_match('/^field(\d+)$/', $key, $matches)) {
        $info = new LotInfo();
        $info->fromArray(array(
          'lot_id'   => $lot->id,
          'field_id' => $matches[1],
          'value'    => $value,
        ));
        $info->save();
        $lot->LotInfo[] = $info;
      }
    }

    // save images
    if ($images) {
      $_images = array();
      foreach ($images as $i => $image) {
        $ext = pathinfo(parse_url($image, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
        $filename = sprintf('%d.%s', $i + 1, $ext);
        $ch = $this->fetcher->getCurl($image, array('referer' => $data['url']));
        if ($data = curl_exec($ch)) {
          file_put_contents($lot->full_image_path . '/' . $filename, $data);
          chmod($lot->full_image_path .'/' . $filename, 0666);
          $_images[] = $filename;
        }
        curl_close($ch);
        if (6 == count($_images)) {
          break;
        }
      }
      if (count($_images)) {
        $lot->images = $_images;
        $lot->thumb = 0;
        $lot->save();
      }
    }

    return $lot->id;
  }

  /**
   * Get address data with coords
   * @param string $address
   * @param string $info=''
   * @return array|false
   */
  public function parseAddress($address, $info = '')
  {
    $splitted = preg_split('/\s*,\s*/', preg_replace('/\s+/', ' ', trim($address)));

    if (in_array($this->region_id, array(77, 78))) {
      $splitted = array_slice($splitted, 2);

      $address1 = array();
      $address1[] = sprintf('г. %s', 77 == $this->region_id ? 'Москва' : 'Санкт-Петербург');

      if (preg_match_all('/м\.\s*(.+),/U', $info, $matches)) {
        foreach ($matches[1] as $node_name) {
          $node = Doctrine::getTable('Regionnode')->createQuery()
            ->where('region_id = ?', $this->region_id)
            ->andWhere('name like ?', $node_name)
            ->andWhere('socr in (?, ?)', array('м', 'м.'))
            ->fetchOne();
          if ($node) {
            $address1[] = $node->full_name;
          }
        }
      }

      $is_city = true;
    }
    else {
      // Remove country
      if (preg_match('/(Р|р)о[cс]+ия/', $splitted[0])) {
        array_shift($splitted);
      }

      // Remove region
      if ($this->isRegionName($splitted[0])) {
        array_shift($splitted);
      }

      // Unset pseude-node
      if (false !== strpos($splitted[0], 'Большой Сочи')) {
        array_shift($splitted);
        if (isset($splitted[0])) {
          $splitted[0] = str_replace('Новый Сочи', 'Сочи', $splitted[0]);
        }
      }

      // if node has children
      if (strpos($splitted[0], 'район') || false !== strpos($splitted[0], 'городской округ') || strpos($splitted[0], 'р-н')) {
        $name = $this->clearParentName($splitted[0]);
        $stmt = Doctrine::getTable('Regionnode')->getConnection()->prepare('
          select id from regionnode where region_id = ? and name = ? and has_children = ?
        ');
        $stmt->execute(array($this->region_id, $name, true));
        $parent_id = $stmt->fetch(Doctrine::FETCH_COLUMN);
        $stmt->closeCursor();

        array_shift($splitted);
      }

      if (empty($splitted[0])) {
        return false;
      }

      // find node
      $node_name = $this->cleanNodeName($splitted[0]);

      if (!empty($parent_id)) {
        $node = Doctrine::getTable('Regionnode')->createQuery()
          ->where('parent = ?', $parent_id)
          ->andWhere('name like ?', $node_name)
          ->fetchOne();
      }

      // if no parent or not found try on 'region level'
      if (empty($node)) {
        $node = Doctrine::getTable('Regionnode')->createQuery()
          ->where('region_id = ?', $this->region_id)
          ->andWhere('name like ?', $node_name)
          ->orderBy('parent is null desc')
          ->fetchOne();
      }

      array_shift($splitted);
      if (!$node) {
        return false;
      }

      $address1 = array();
      $address1[] = Doctrine::getTable('Region')->find($this->region_id)->name;
      if ($node->parent) {
        $address1[] = $node->Regionnode->full_name;
      }
      $address1[] = $node->full_name;

      $is_city = 'г' == $node->socr;
    }


    $data = array(
      'address1' => implode(', ', $address1),
      'address2' => implode(', ', $splitted),
      'is_city'  => $is_city,
    );


    // add lat-lng
    if ($data['address2'] || !$is_city) {
      if (in_array($this->region_id, array(77, 78))) {
        $geodata = Geocoder::getPlacemark(sprintf(
          'г. %s, %s', 77 == $this->region_id ? 'Москва' : 'Санкт-Петербург', $data['address2']
        ));
      }
      else {
        $geodata = Geocoder::getPlacemark(sprintf('%s, %s', $data['address1'], $data['address2']));
      }

      if (isset($geodata->Point->coordinates)) {
        $data['latitude'] = $geodata->Point->coordinates[1];
        $data['longitude'] = $geodata->Point->coordinates[0];
      }
    }

    return $data;
  }


  private function isRegionName($name)
  {
    return preg_match(
      '/((О|о)бласт(ь|и)|обл\.?$|(К|к)рай|(Р|р)еспублика|(О|о)круг|Башкортостан|Карелия|Чувашия)/',
      $name
    );
  }

  private function clearParentName($name)
  {
    return trim(str_replace(
      array('район', 'городской округ', 'р-н'),
      '', $name
    ));
  }

  private function cleanNodeName($name)
  {
    return trim(preg_replace(array(
      '/поселок подсобного хозяйства /',
      '/поселок (городского|сельского) типа /',
      '/(городской|коттеджный|рабочий) пос(е|ё)лок /',
      '/пос(е|ё)лок (альплагерь |станции |кордон )?/',
      '/ пос\.|пос\. /',
      '/деревня /',
      '/(д.\ | д\.)/',
      '/село /',
      '/колхоз /',
      '/садовое товарищество /',
      '/садоводство /',
      '/(^г\.? | г\.| город|^город )/',
      '/при ж\/д станции/',
      '/ж\/д станция/',
      '/станция/',
      '/хутор/',
      '/станица/',
    ), '', $name));
  }
}