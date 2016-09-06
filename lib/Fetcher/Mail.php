<?php

/**
 * Class for fetching http://realty.mail.ru/ lots
 *
 * @author Grechko Aleksey
 */
class Fetcher_Mail extends BaseFetcher
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
          'strip_html_options'  => array(),
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
    $data['params'] = array();

    preg_match('/<th>Дата добавления<\/th>\s*<td>([^<]+)/i', $html, $matches);
    if (!empty($matches[1])) {
      $tmp = explode('.', $matches[1]);
      $data['date'] = sprintf('%s %s %s', $tmp[0], $this->months[$tmp[1]], $tmp[2]);
      unset($tmp);
    }

    preg_match('/<\/b>\s*\(([^<]+)<span class=\'gray\'>руб.<\/span>\/год за м.<small><sup>2<\/sup><\/small>\)/i', $html, $matches);
    if (!empty($matches[1])) {
      $data['price'] = intval(preg_replace('/\D+/', '', $matches[1]));
    } else {
      preg_match('/<th>Цена\s*<\/th><td>\s*<b>([^<]+)/i', $html, $matches);
      if (!empty($matches[1])) {
        $data['price'] = intval(preg_replace('/\D+/', '', $matches[1]));
      }
    }
    if (empty($data['price']) || !($data['price'] > 0)) {
      ParseLogger::writeError($url, ParseLogger::EMPTY_PRICE);
      return false;
    } elseif ($this->lot_options['type'] == 'commercial-rent') {
      $data['params']['Арендная ставка кв.м/год'] = $data['price'];
    }

    preg_match('/<th>Адрес<\/th><td>\s*(?:<a.+?img.+?>)*(.+?)(?:<\/a>)*\s*<\/td>/is', $html, $matches);
    if (!empty($matches[1])) {
      $data['title']['address'] = preg_replace('/^&[^;]+;,*\s*/i', '', $matches[1]);
    }

    preg_match('/<th>Метро, удаленность<\/th>\s*<td>(?:([^<;,]+)|<a[^>]+>([^<]+)<\/a>)/is', $html, $matches);
    if (!empty($matches[1])) {
      $data['title']['metro'] = preg_replace('/\s*,.*$/is', '', $matches[1]);
    } elseif (!empty($matches[2])) {
      $data['title']['metro'] = preg_replace('/\s*,.*$/is', '', $matches[2]);
    }

    preg_match('/<tr>\s*<td.[^<>]+?class="pt25.[^<>]+?>(.+?)<\/td/ims', $html, $matches);
    if (!empty($matches[1])) {
      $data['description'] = $matches[1];
      $data['description'] = preg_replace('/<span id="descr_hide">[^>]+>/i', '', $data['description']);
      $data['description'] = preg_replace('/<a.[^>]+>[^>]+>/i', '', $data['description']);
      $data['description'] = preg_replace('/<[^>]+>/i', '', $data['description']);
    }

    preg_match('/<th>Контакт<\/th><td[^<>]*?>\s*<b>([^<]+)/is', $html, $matches);
    if (!empty($matches[1])) {
      $data['organization_contact_phone'] = preg_replace('/&[^;]+;/', ' ', $matches[1]);
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

    preg_match('/<th>Контактное лицо<\/th><td[^>]*>(.+?)<\/td>/is', $html, $matches);
    if (!empty($matches[1])) {
      $data['organization_contact_name'] = $matches[1];
    } else {
      preg_match('/<th>Контактное лицо<\/th><td[^>]*>\s*<noindex>\s*(?:<a[^>]+>)*\s*([^<]+)/is', $html, $matches);
      if (!empty($matches[1])) {
        $data['organization_contact_name'] = $matches[1];
      }
    }

    preg_match('/http:\/\/maps.mail.ru\/\?ll=([\d.]+),([\d.]+)/i', $html, $matches);
    if (!empty($matches[1]) && !empty($matches[2])) {
      $data['latitude']  = $matches[2];
      $data['longitude'] = $matches[1];
    }

    preg_match('/<img[^>]+(?:src="([^"]+)"[^>]+id="big_image"|id="big_image"[^>]+src="([^"]+)")/is', $html, $matches);
    if (!empty($matches[1])) {
      $data['photos'][] = str_replace('/250/', '/500/', $matches[1]);
    } elseif (!empty($matches[2])) {
      $data['photos'][] = str_replace('/250/', '/500/', $matches[2]);
    }

    preg_match('/href="\/detailpic\/\d+\.html">Изображения \((\d+)\)<\/a>/is', $html, $matches);
    if (!empty($matches[1]) && intval($matches[1]) > 1) {
      for ($i=1; $i<intval($matches[1]); $i++) {
        $data['photos'][] = str_replace('_0.jp', '_'.$i.'.jp', $data['photos'][0]);
      }
    }

    preg_match_all('/<th>(.+?)<\/th>\s*<td.*?>(.+?)<\/td>/ims', $html, $matches);
    if (!empty($matches[1])) {
      foreach ($matches[1] as $i => $param_name) {
        $data['params'][trim($param_name)] = trim($matches[2][$i]);
      }
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
    foreach ($params as $key => &$value) {
      $this->progress();
      if (in_array($key, array('Метро, удаленность','Адрес','Цена','Новостройка','Контактное лицо','Контакт','Кол-во просмотров объявления','Дата добавления'))) {
        unset($params[$key]);
        continue;
      }

      if ($key == 'Количество комнат' && mb_strpos($this->lot_options['type'], 'house') === false) {
        unset($params[$key]);
        $value = $value.ending($value, '', '-х', '-ти', '-ми').' комнатная квартира';
        $params = array_merge($params, array('Тип предложения' => $value));
        continue;
      }

      if ($key == 'К/КВ') {
        unset($params[$key]);
        $value = 'комната';
        $params = array_merge($params, array('Тип предложения' => $value));
        continue;
      }

      if ($key == 'Тип дома' && mb_strpos($this->lot_options['type'], 'house') === false) {
        unset($params[$key]);
        $params = array_merge($params, array('Тип здания' => $value));
      }

      if ($key == 'Тип объекта') {
        unset($params[$key]);
        switch ($value) {
          case 'ТП':
            $value = 'Торговые площади';
            break;
          case 'ПСУ':
            $value = 'Развлекательный';
            break;
          case 'Здание':
          case 'ОСЗ':
            $value = 'Отд. стоящее здание';
            break;
          case 'ПСН':
          case 'Нежилое помещение':
            $value = 'Свободного назначения';
            break;
          case 'Земельный участок под склад':
            $value = 'Земля';
            break;
          case 'Офис':
          case 'Склад':
            $value = $value;
            break;
          default:
            $value = 'Другое';
        }

        $params = array_merge($params, array('Тип недвижимости' => $value));
        continue;
      }

      if ($key == 'Этаж / Этажность') {
        unset($params[$key]);
        if (mb_strpos($value, '/', null, 'utf-8')) {
          $add = array_combine(
              explode('/', preg_replace('/\s+/is', '', $key)),
              explode('/', preg_replace('/\s+/is', '', $value))
            );
          $params = array_merge($params, $this->parseLotParams($add));
        }
        continue;
      }

      if (mb_stripos($key, '<sup>', null, 'utf-8')) {
        unset($params[$key]);
        $key = mb_substr($key, 0, mb_stripos($key, ',', null, 'utf-8'), 'utf-8');
        $value = str_replace(',', '.', preg_replace('/\s+/is', '', $value));

        if ($key == 'Общая площадь' && mb_strpos($this->lot_options['type'], 'commercial') !== false) {
          $key = 'Общая площадь помещения';
        } elseif ($key == 'Площаль кухни') {
          $key = 'Площадь кухни';
        }
        if (preg_match('/до/i', $value)) {
          $vals = preg_split('/\s*до\s*/i', $value);
          $value = (preg_replace('/[^\d.,]/', '', $vals[0])+preg_replace('/[^\d.,]/', '', $vals[1]))/2;
        }
        $params = array_merge($params, array($key => $value));
      } elseif (is_string($value)) {
        $value = preg_replace('/<.+>/isU', '', $value);
      }

      if (preg_match('/^[\d\s.,]+$/mi', $value)) {
        $value = str_replace(',', '.', preg_replace('/[^\d.,]+[^\d]/', '', $value));
      } elseif (is_string($value)) {
        $value = preg_replace('/^\s+|\s+$/', '', $value);
      }

      if (empty($value) || $value == '&ndash;') {
        unset($params[$key]);
      }
    }

    return $params;
  }


  /**
   * Parse address row
   * @param string $value
   * @return array $address
   */
  protected function parseLotAddress($value)
  {
    $region = ParseTools::getRegionName($this->lot_options['region_id']);

    $address1 = $region;
    $address2 = '';

    $tmp_arr = explode(',', $value['address']);

//    if ($this->lot_options['region_id'] == 47) {
//      $city = array_shift($tmp_arr);
//      $address2 = implode(' , ', $tmp_arr);
//    } else {
//      $city = array_pop($tmp_arr);
//      $address2 = implode(' , ', $tmp_arr);
//    }

    $city = array_pop($tmp_arr);
    $address2 = implode(',', $tmp_arr);

    $city = trim($city);
    $city = preg_replace('/,\s*/is', '', $city);
    $city = preg_replace('/(^|\s)[а-яА-Я.]{1,3}(\s|$)/iu', '', $city);

    if ($this->lot_options['region_id'] != 77 && $this->lot_options['region_id'] != 78) {
      $address1 .= ', '.$city;
    } elseif (!empty($value['metro'])) {
      $address1 .= ', м. '.$value['metro'];
    }

    $address2 = str_replace('№', '', $address2);
    $address2 = preg_replace('/(^|,*\s)[^,]+(район|р-н|обл\.*),\s*/isu', '', $address2);
    $city = preg_replace('/\s+/iu', '[^,]*', $city);
    $address2 = preg_replace('/(^|,*\s)[^,]*'.str_replace(array('\\','/'), array('\\\\','\\'), $city).'(\s|,|$)+/isu', ', ', $address2);
    $address2 = preg_replace('/(^|,\s*)\d+[^,]+ от [^,]+/isu', '', $address2);
    $address2 = preg_replace('/[,\s]*рядом с [^,]+/isu', '', $address2);
    $address2 = preg_replace('/,* (д|дом)(\.|\s)\s*/isu', ', ', $address2);
    $address2 = preg_replace('/\s*\([^)]*\)/', '', $address2);
    $address2 = preg_replace('/,[^,](\d+),*\s(корпус\s|корп(?:\.*|\s+)|кор(?:\.*|\s+)|стр(?:\.*|\s+)|строение\s)+\s*(\d+)/isu', ', $1 $2 $3', $address2);
    $address2 = preg_replace('/^\s+|\s+$|^\s*,\s*|,\s*$/', '', $address2);

    $data = array(
      'address1' => $address1,
      'address2' => $address2,
    );

    return $data;
  }


  /**
   * Extract lot and page links
   * @param string $html
   * @return void|false
   */
  protected function extractLinks($html)
  {
    exit;
    // first find lot links
    //foreach ($this->parser->getElementById('idListing')


    preg_match_all('/href="(\/detail\/\d+\.html)".+?<span class="date[^>]+>(.+?)<\/span>\s*<span[^>]+class="contact[^>]+>\s*(.+?)\s*<\/span>/is', $html, $matches);
    foreach ($matches[1] as $i => $link) {
      $link = 'http://realty.mail.ru'.$link;
      $this->progress();

      if (preg_match('/открыть /i', $matches[3][$i])) continue;

      if (!in_array($link, $this->lots)) {
        $query = Doctrine_Query::create(ProjectConfiguration::getActive()->getMasterConnection());
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
    preg_match_all('/href="([\w\d\/?&=]+page=.[^<>]+?)">(?:\d|Далее)/is', $html, $matches);
    foreach ($matches[1] as $link) {
      $link = 'http://realty.mail.ru'.$link;
      $this->progress();
      if ($link) {
        if (!in_array($link, $this->pages)) {
          preg_match('/page=(\d+)/i', $link, $page);
          if (in_array($page[1], $this->page_numbers)) continue;

          $this->pages[] = $link;
          $this->page_numbers[] = $page[1];
        }
      }
    }

    return true;
  }


  protected function translateParamValue($value)
  {
    switch ($value) {
      case 'да':
        return 'есть';

      case 'есть':
      case 'нет':
        return $value;
    }

    return null;
  }


  protected function cropLotImage($image, $width, $height)
  {
    $image_obj = new Imagick($image);
    if (!$image_obj->cropImage($width, $height-25, 0, 0)) {
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
