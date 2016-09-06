<?php

/**
 * Class for fetching http://realty.dmir.ru/ lots
 *
 * @author Eugeniy Belyaev & Aleksey Grechko
 */
class Fetcher_Dmir extends BaseFetcher
{
  private
    $months = array(
      '01' => 'jan', '02' => 'feb', '03' => 'mar', '04' => 'apr',
      '05' => 'may', '06' => 'jun', '07' => 'jul', '08' => 'aug',
      '09' => 'sep', '10' => 'oct', '11' => 'nov', '12' => 'dec',
    );


  protected function importLot($url)
  {
    try {
      $html = $this->fetch($url, array(
          'strip_comments'      => true,
          'strip_html'          => true,
          'strip_html_options'  => array('script'),
          'only_body'           => false,
          'use_proxy'           => $this->limit > 10,
        ));
    }
    catch (Exception $e) {
      printf('%s%s', $e->getMessage(), PHP_EOL);
      return false;
    }

    $this->progress();
    $data = $this->lot_options;
    $data['parsed_at'] = date('Y-m-d H:i:s');
    $data['organization_link'] = $url;
    $data['currency'] = 'RUR';
    $data['exchange'] = 1;
    $data['photos'] = array();
    if (!isset($data['params'])) {
      $data['params'] = array();
    }

    $this->parser->load($html);
    $container = $this->parser->find('dl.card', 0);

    if ($title = @$this->parser->getElementById('divMain')->find('section.middle', 0)->find('figure', 0)->find('h1', 0)->innertext) {
      list($type, $address) = explode(',', $title, 2);
      $address = preg_replace('/<.+?>|&[^;]+;/is', ' ', $address);
      $address = preg_replace('/\s+/', ' ', $address);
      $data['title'] = trim($address);

      if (empty($data['params']['Тип предложения']) && strpos($this->lot_options['type'], 'apartament') !== false) {
        if (preg_match('/^(?:Продаю|Сдаю)\s+(\d+)\s+комн/iu', $type, $matches)) {
          $data['params']['Тип предложения'] = $matches[1].ending($matches[1], '', '-х', '-ти', '-ми').' комнатная квартира';
        }
        elseif (preg_match('/^(?:Продаю|Сдаю)\s+комнату/iu', $type, $matches)) {
          $data['params']['Тип предложения'] = 'комната';
        }
      }
      elseif (empty($data['params']['Тип недвижимости']) && strpos($this->lot_options['type'], 'commercial') !== false) {
        $data['params']['Тип недвижимости'] = ParseTools::matchCommercialType($type);
      }
    }
    if (empty($data['title'])) {
      ParseLogger::writeError($url, ParseLogger::EMPTY_ADDRESS);
      return false;
    }

    if ($fragment = $container->getElementById('price_offer')) {
      $data['price'] = intval(preg_replace('/\D+/', '', $fragment->find('span', 0)->innertext));
    }
    if (empty($data['price']) || !($data['price'] > 0)) {
      ParseLogger::writeError($url, ParseLogger::EMPTY_PRICE);
      return false;
    }

    preg_match('/-(\d+)\//', $data['organization_link'], $matches);
    if ($fragment = $container->getElementById('object_phones_complain_'.$matches[1])) {
      $phones = array();
      foreach ($fragment->find('div.phone') as $phone) {
        if (preg_match('/\d/', $phone->innertext)) {
          $phones[] = $phone->innertext;
        }
      }
      $data['organization_contact_phone'] = implode(', ', $phones);
    }

    if ($fragment = $container->getElementById('contacts_data')) {
      preg_match('/<dt>Размещено<\/dt><dd[^>]*>(?:<[^>]+>)*([\d.\-\s]+)(?:<[^>]+>)*</i', $fragment->innertext, $matches);
      if (!empty($matches[1])) {
        $data['date'] = $matches[1];
      }

      preg_match('/<dt>(?:Разместил\(а\)|Контактное лицо)<\/dt><dd[^>]*>(?:<[^>]+>)*([^<]+)(?:<\/[^>]+>)*</i', $fragment->innertext, $matches);
      if (!empty($matches[1])) {
        $data['organization_contact_name'] = $matches[1];
      }
      else {
        preg_match('/<dt>Компания<\/dt><dd[^>]*>(?:<[^>]+>)*([^<]+)(?:<\/[^>]+>)*</i', $fragment->innertext, $matches);
        if (!empty($matches[1])) {
          $data['organization_contact_name'] = 'Компания '.$matches[1];
        }
      }
    }

    if ($fragment = $container->getElementById('full_info')) {
      $data['description'] = $fragment->innertext;
    }
    elseif ($fragment = $container->find('h2.price', 0)->nextSibling()->find('div', 0)) {
      $data['description'] = $fragment->innertext;
    }

    if (!empty($data['description']) && empty($data['organization_contact_phone'])) {
      preg_match('/(?:Тел(?:ефон|\.)*|Т\.):*\s*([0-9-+()]+[0-9-+(), ]*)/isu', $data['description'], $matches);
      if (!empty($matches[1])) {
        $data['organization_contact_phone'] = $matches[1];
      }
    }
    if (empty($data['organization_contact_phone'])) {
      ParseLogger::writeError($url, ParseLogger::EMPTY_PHONE);
      return false;
    }

    $this->progress();

    if ($fragment = $this->parser->getElementById('divMain')->find('figure.cardmap', 0)) {
      foreach ($fragment->find('script') as $f) {
        preg_match('/LatLng\(([\d.]+),\s*([\d.]+)\s*\)/', $f->innertext, $matches);
        if (!empty($matches[1]) && !empty($matches[2])) {
          $data['latitude']  = $matches[1];
          $data['longitude'] = $matches[2];
          break;
        }
      }
    }

    if ($fragment = $container->find('script', 0)) {
      preg_match('/photoIds\s+=\s+\[([\d,]+)\]/i', $fragment->innertext, $matches);
      if (!empty($matches[1])) {
        $data['photos'] = explode(',', $matches[1]);
      }
    }

    foreach ($container->find('ul.parametres') as $item) {
      foreach ($item->firstChild()->find('li') as $li) {
        preg_match('/<b>([^<]+)(?:<sup>2<\/sup>)*<\/b>(.+)$/is', $li->innertext, $matches);
        if (!empty($matches[1]) && !empty($matches[2])) {
          $data['params'][trim($matches[2])] = trim($matches[1]);
        }
        $li->clear();
      }

      $item->clear();
    }


    ParseTools::preg_clear_cache();
    if (is_object($fragment)) $fragment->clear();
    $container->clear();
    $this->parser->clear();
    unset($item, $fragment, $container, $html);

    $this->progress();
    $data = $this->parseLotData($data);

    return $data;
  }


  /**
   * Fix lot additional params
   * @param array $params
   * @return array $params
   */
  protected function parseLotParams(array $params)
  {
    $checked = array();
    foreach ($params as $key => $value) {
      $this->progress();

      $key = mb_strtoupper(mb_substr($key, 0, 1, 'utf-8'), 'utf-8').mb_substr($key, 1, mb_strlen($key, 'utf-8'), 'utf-8');
      if (in_array($key, array('Комната',
                              'Комнат',
                              'Комнат в продаже',
                              'Комната в продаже',
                              'Комнаты в продаже',
                              'Площадь комнат',
                              'Потолки',
                              'Серия',
                              'Балкона',
                              'Лоджии',
                              'Лифта',
                              'Мусоропровода',
                              'Интернета',
                              'Телефона',
                              'Охраны',
                              'Парковки',
                              'Домофона',
                              'Холодильника',
                              'Стиральной машины',
                              'Телевизора',
                              'Кондиционера',
                              'Мебели'))) {
        continue;
      }

      if ($key == 'Общая площадь' && mb_strpos($this->lot_options['type'], 'house') !== false) {
        $key = 'Площадь дома';
      }
      elseif ($key == 'Класс здания' && $this->lot_options['type'] == 'commercial-sale') {
        $key = 'Класс офиса';
      }
      elseif ($key == 'Состояние' && $this->lot_options['type'] == 'commercial-sale') {
        $key = 'Ремонт';
      }

      if ($key == 'Дом') {
        switch ($this->lot_options['type']) {
          case 'apartament-sale':
          case 'apartament-rent':
          case 'commercial-sale';
          case 'commercial-rent';
            $key = 'Тип здания';
            break;

          case 'house-sale';
          case 'house-rent';
            $key = 'Тип дома';
            break;
        }
      }

      if ($key == 'Ремонт') {
        switch ($this->lot_options['type']) {
          case 'apartament-sale': $key = 'Ремонт';  break;
          case 'apartament-rent': $key = 'Состояние/ремонт';  break;
          case 'commercial-rent': $key = 'Состояние, отделка, готовность';  break;
          case 'commercial-sale': $key = 'Состояние, отделка';  break;
          case 'house-rent':      $key = 'Ремонт/состояние';  break;
          case 'house-sale':      $key = 'Ремонт/состояние';  break;
        }
      }

      if ($key == 'Участок') {
        switch ($this->lot_options['type']) {
          case 'house-sale':
          case 'house-rent':
            $key = 'Площадь участка';
            break;

          case 'commercial-sale':
          case 'commercial-rent':
            $key = 'Общая площадь земли';
            break;
        }
      }

      if (in_array($key, array('Мусоропровод',
                              'Лифт',
                              'Интернет',
                              'Телефон',
                              'Охрана',
                              'Парковка',
                              'Домофон',
                              'Холодильник',
                              'Стиральная машина',
                              'Телевизор',
                              'Кондиционер',
                              'Мебель',
                              ))) {
        $key = mb_strtolower($key, 'utf-8');

        if ($value == 'есть') {
          if (!empty($checked['Детали'])) {
            $checked['Детали'] .= ', '.$key;
          } else {
            $checked['Детали'] = $key;
          }
        }
        continue;
      }
      if ($key == 'Балкон' || $key == 'Лоджия') {
        if ($value == 'есть') {
          if (isset($checked['Балкон/лоджия'])) {
            $checked['Балкон/лоджия'] .= ' и ';
          }
          else {
            $checked['Балкон/лоджия'] = '';
          }
          $checked['Балкон/лоджия'] .= mb_strtolower($key, 'utf-8');
        }

        continue;
      }

      if (mb_stripos($key, 'площадь', null, 'utf-8') !== false) {
        $value = str_replace(',', '.', preg_replace('/[^\d.,]/', '', $value));
      }

      if (empty($value)) continue;

      $checked[$key] = $value;
    }

    return $checked;
  }


  /**
   *
   * @param string $value
   * @return string
   */
  protected function parseLotDate($value)
  {
    preg_match('/\.(\d+)\./', $value, $matches);
    if (!empty($matches[1])) {
      $value = preg_replace('/\.(\d+)\./', sprintf(' %s ', $this->months[$matches[1]]), $value);
    }

    return $value;
  }


  /**
   *
   * @param array $value
   * @return array
   */
  protected function parseLotPhotos(array $value)
  {
    $photos = array();
    foreach ($value as $i => $item) {
      $photos[] = sprintf('http://realty.dmir.ru/ObjectImg.axd?id=%s&type=photo', $item);
    }

    return $photos;
  }


  /**
   * Parse address row
   * @param string $value
   * @return array $address
   */
  protected function parseLotAddress($value)
  {
    $value = preg_replace('/\s+/', ' ', $value);
    $data = array(
      'address1' => '',
      'address2' => '',
    );
    
    if ($this->lot_options['region_id'] != 77 && $this->lot_options['region_id'] != 78) {
      $data['address1'] = ParseTools::getRegionName($this->lot_options['region_id']);

      preg_match('/ш.\s*([^,$]+)/iu', $value, $matches);//shosse
      if (!empty($matches[1])) {
        $data['address2'] .= $matches[1].' ш.';

        preg_match('/\s+(\d+(?:[,.\d])*\s+км.)/iu', $value, $matches);//km
        if (!empty($matches[1])) {
          $data['address2'] .= ', '.$matches[1];
        }

        preg_match('/,\s+([^,]+)$/iu', $value, $matches);//naselenniy punkt
        if (!empty($matches[1]) && !preg_match('/\sкм.|^ш./iu', $matches[1])) {
          $data['address1'] .= ', '.$matches[1];
        }
      }
      else {
        $value = explode(', ', $value, 3);

        switch (count($value)) {
          case 3:
            $data['address1'] .= ', '.$value[0];
            $data['address2'] .= $value[1].', '.$value[2];
            break;

          case 2:
            $data['address1'] .= ', '.$value[0];
            $data['address2'] .= $value[1];
            break;

          case 1:
            $data['address1'] .= ', '.$value[0];
            break;
        }

        preg_match('/.+,.+\s(.+ р-он)/isu', $data['address1'], $rn);
        if (!empty($rn[1])) {
          $data['address1'] = preg_replace('/,*\s[^\s]+\sр-он/isu', '', $data['address1']);
          if (!empty($data['address2'])) {
            $data['address2'] .= ', '.$rn[1];
          }
          else {
            $data['address2'] = $rn[1];
          }
        }
      }
    }
    else {
      $value = explode(', ', $value, 4);

      switch (count($value)) {
        case 4:
          $data['address1'] = $value[0].', '.$value[1];
          $data['address2'] = $value[2].', '.$value[3];
          break;

        case 3:
          $data['address1'] = $value[0].', '.$value[1];
          $data['address2'] = $value[2];
          break;
      }
    }

    return $data;
  }


  /**
   * Extract lot and page links
   * @param string $html
   * @return void|false
   */
  protected function extractLinks($html)
  {
    $this->parser->load($html);
    $container = $this->parser->getElementById('divMain');
    if (!$container || $container->find('.notfound')) return false;

    // first find lot links
    foreach ($container->find('ul.resultlist', 0)->find('li[id]') as $item) {
      $this->progress();
      if (!$item->find('dl', 0)) continue;
      if ($a = $item->find('dl', 0)->find('dt', 0)->find('a', 0)) {
        if (!$this->appendLotLink('http://realty.dmir.ru'.str_replace('http://realty.dmir.ru', '', $a->href))) {
          return false;
        }
      }
      $item->clear();
    }

    // ... after extract page locations
    if ($paginator = $container->find('div.pagination', 0)) {
      foreach ($paginator->find('a') as $item) {
        $this->progress();
        if (ctype_digit($item->innertext) && $item->innertext > 1 && !empty($item->href) && $item->href != '#') {
          $link = 'http://realty.dmir.ru'.str_replace('http://realty.dmir.ru', '', $item->href);
          if (!isset($this->pages[$item->innertext])) {
            $this->pages[$item->innertext] = $link;
          }
        }

        $item->clear();
      }
    }

    $this->parser->clear();
    unset($a, $item, $paginator, $container, $html);

    return true;
  }


  protected function translateParamValue($value)
  {
    switch (mb_strtolower($value, 'utf-8')) {
      case 'да':
        return 'есть';

      case 'есть':
      case 'нет':
        return $value;

      case 'дизайнерский':
      case 'евро':
        return 'евроремонт';

      case 'без ремонта':
      case 'отсутствует':
        return false;

      case 'есть':
      case 'косметика':
      case 'хороший':
        return 'после косметического ремонта';

      case 'деревянный':
        return 'Дерево';

      case 'инвестпроект':
        return 'промышленные';

      case 'ижс':
        return 'поселений';

      case 'садоводство':
        return 'сельхоз';

      case 'центральное газовое':
        return 'газовое';

      case 'печь':
        return 'печь или камин';
    }

    return null;
  }


  protected function cropLotImage($image, $width, $height)
  {
    $image_obj = new Imagick($image);
    if (!$image_obj->cropImage($width, $height-55, 0, 0)) {
      return false;
    }
    if (!$image_obj->writeImage($image)) {
      return false;
    }

    $image_obj->destroy();
    unset($image_obj);

    return true;
  }
}