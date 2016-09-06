<?php

/**
 * Class for fetching http://www.mirkvartir.ru/ lots
 *
 * @author Grechko Aleksey
 */
class Fetcher_Mirkvartir extends BaseFetcher
{
  private
    $months = array(
      'января'    => 'january',   'февраля' => 'february',
      'марта'     => 'march',     'апреля'  => 'april',
      'мая'       => 'may',       'июня'    => 'june',
      'июля'      => 'july',      'августа' => 'august',
      'сентября'  => 'september', 'октября' => 'october',
      'ноября'    => 'november',  'декабря' => 'december',
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
    $container = $this->parser->getElementById('common');

    if ($fragment = @$this->parser->getElementById('structure')->find('td.content', 0)->find('p.price', 0)) {
      preg_match('/<strong[^>]*>([^<]+)<\/strong>/is', $fragment->innertext, $matches);
      if (!empty($matches[1])) {
        $data['price'] = intval(preg_replace('/\D+/', '', $matches[1]));
      }
    }
    if (empty($data['price']) || !($data['price'] > 0)) {
      ParseLogger::writeError($url, ParseLogger::EMPTY_PRICE);
      return false;
    }

    if ($fragment = $container->find('div.gray_block', 0)) {
      preg_match('/<dt[^>]*>Адрес<\/dt>\s*<dd[^>]*>(.+?)<\/dd>/is', $fragment->innertext, $matches);
      if (!empty($matches[1])) {
        $data['title'] = explode(',', preg_replace('/\s*<.+?>\s*/is', '', $matches[1]));

        preg_match('/<dt[^>]*>Метро<\/dt>\s*<dd[^>]*>(.+?)<\/dd>/is', $fragment->innertext, $matches);
        if (!empty($matches[1])) {
          $metro = preg_replace('/\s*<.+?>\s*/is', '', $matches[1]);
          $metro = preg_replace('/,\s*.+/isu', '', $metro);
          $data['title']['metro'] = $metro;
        }
        preg_match('/<dt[^>]*>Район<\/dt>\s*<dd[^>]*>(.+?)<\/dd>/is', $fragment->innertext, $matches);
        if (!empty($matches[1])) {
          $data['title']['district'] = preg_replace('/\s*<.+?>\s*/is', '', $matches[1]);
        }
      }
    }
    if ($fragment = @$this->parser->getElementById('structure')->find('td.content', 0)->find('div.twocolshead', 0)->find('h1.s2', 0)) {
      preg_match('/<span>[^<]*<\/span>(.+?)$/is', $fragment->innertext, $matches);
      if (!empty($matches[1])) {
        $data['title']['address'] = trim(preg_replace('/<[^>]+>/s', '', $matches[1]));
      }
    }
    if (empty($data['title'])) {
      ParseLogger::writeError($url, ParseLogger::EMPTY_ADDRESS);
      return false;
    }

    if ($fragment = $container->find('p.estate-description', 0)) {
      $data['description'] = $fragment->plaintext;
    }

    if ($fragment = $container->find('p.contact-item')) {
      foreach ($fragment as $f) {
        preg_match('/<span[^>]*>Телефон:[^<]+<\/span>\s*<span[^>]*>([^<]+)/is', $f->innertext, $matches);
        if (!empty($matches[1])) {
          $data['organization_contact_phone'] = preg_replace('/\s*\([^)]+\)$/is', '', $matches[1]);
          $data['organization_contact_phone'] = str_replace(';', ',', $matches[1]);
        }
        elseif (preg_match('/<b[^>]*>[а-яa-z\s-$;,.]/iu', $f->innertext)) {
          preg_match('/<b[^>]*>([^<]+)<\/b>/', $f->innertext, $matches);
          if (!empty($matches[1])) {
            $data['organization_contact_name'] = preg_replace('/\s*&[\w]+;\s*/is', '', $matches[1]);
          }
        }

        $f->clear();
      }

      unset($f);
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

    foreach ($container->find('time') as $item) {
      if ($item->getAttribute('itemprop') == 'availabilityStarts') {
        $data['date'] = $item->getAttribute('datetime');
        break;
      }
    }

    $this->progress();

    if ($fragment = $container->find('div.techinfo', 0)) {
      preg_match('/<td class="label">Oбновлено<\/td>\s*<td[^>]*>([^<(]+) г\.[^<]*<\/td>/is', $fragment->innertext, $matches);
      if (!empty($matches[1])) {
        $data['date'] = $matches[1];
      }

      if (empty($data['organization_contact_name'])) {
        preg_match('/<td class="label">Компания<\/td>\s*<td[^>]*>(?:<a[^>]+>)([^<]+)/is', $fragment->innertext, $matches);
        if (!empty($matches[1])) {
          $matches[1] = preg_replace('/\s*<.+?>\s*/is', '', $matches[1]);
          if (mb_stripos($matches[1], ',', null, 'utf-8') !== false) {
            $data['organization_contact_name'] = 'Компания '.mb_substr($matches[1], 0, mb_stripos($matches[1], ',', null, 'utf-8'), 'utf-8');
          } else {
            $data['organization_contact_name'] = 'Компания '.$matches[1];
          }
        }
      }
    }

    if ($fragment = $this->parser->getElementById('gm_default')) {
      if ($fragment = $fragment->find('script', 0)) {
        preg_match('/"Latitude"\:([\d.]+),\s*"Longitude"\:([\d.]+),/i', $fragment->innertext, $matches);
        if (!empty($matches[1]) && !empty($matches[2])) {
          $data['latitude']  = $matches[1];
          $data['longitude'] = $matches[2];
        }
      }
    }

    if ($photos = $this->parser->getElementById('photos')) {
      foreach ($photos->find('img.photo-item-img') as $item) {
        $data['photos'][] = $item->src;
        $item->clear();
      }
    }


    if ($fragment = $container->find('div.objparams', 0)) {
      foreach ($fragment->find('tr.info-item') as $item) {
        if (($k = $item->find('td.item-label', 0)) && ($v = $item->find('td.item-content', 0))) {
          $data['params'][trim($k->innertext)] = trim($v->innertext);
        }

        $item->clear();
      }

      unset($item);
    }


    ParseTools::preg_clear_cache();
    if (is_object($photos)) $photos->clear();
    if (is_object($fragment)) $fragment->clear();
    $container->clear();
    $this->parser->clear();
    unset($item, $photos, $fragment, $container, $html);

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
      if (in_array($key, array('Oбновлено','Источник','Компания','Серия дома'))) {
        unset($params[$key]);
        continue;
      }

      if ($key == 'Кол-во комнат') {
        unset($params[$key]);
        if (empty($params['Тип предложения'])) {
          $key = 'Тип предложения';
          $value = $value.ending($value, '', '-х', '-ти', '-ми').' комнатная квартира';
          $params = array_merge($params, array($key => $value));
        }
        continue;
      }

      if ($key == 'Этаж / этажность') {
        unset($params[$key]);
        if (mb_strpos($value, '/', null, 'utf-8')) {
          $add = array_combine(
              explode('/', preg_replace('/\s+/is', '', $key)),
              explode('/', preg_replace('/\s+/is', '', $value))
            );
          $params = array_merge($params, $add);
        }
        continue;
      }

      if ($key == 'Материал стен') {
        unset($params[$key]);
        $params = array_merge($params, array('Тип здания' => $value));
      }
      elseif ($key == 'Год постройки/ сдачи') {
        unset($params[$key]);
        $params = array_merge($params, array('Год постройки' => $value));
      }

      if (mb_stripos($key, 'Состояние', null, 'utf-8') !== false) {
        unset($params[$key]);
        switch ($this->lot_options['type']) {
          case 'apartament-sale': $key = 'Ремонт';  break;
          case 'apartament-rent': $key = 'Состояние/ремонт';  break;
          case 'commercial-rent': $key = 'Состояние, отделка, готовность';  break;
          case 'commercial-sale': $key = 'Состояние, отделка';  break;
          case 'house-rent':      $key = 'Ремонт/состояние';  break;
          case 'house-sale':      $key = 'Ремонт/состояние';  break;
        }

        $params = array_merge($params, $this->parseLotParams(array($key => $value)));
        continue;
      }

      if ($key == 'Площадь, м<sup>2</sup>' || $key == 'Площадь,&nbsp;м<sup>2</sup>') {
        unset($params[$key]);
        $value = preg_replace('/\s*\([^)]+\)/is', '', $value);
        if (mb_strpos($value, '/', null, 'utf-8')) {
          $areas = array_combine(
              array('Общая площадь','Жилая площадь','Площадь кухни'),
              explode('/', str_replace(',', '.', preg_replace('/\s+/is', '', $value)))
            );
        }
        $params = array_merge($params, $areas);
        continue;
      } elseif (is_string($value)) {
        $value = preg_replace('/<.+>/isU', '', $value);
      }

      if (preg_match('/^[\d\s.,]+$/mi', $value)) {
        $value = str_replace(',', '.', preg_replace('/[^\d.,]+[^\d]/', '', $value));
      } elseif (is_string($value)) {
        $value = preg_replace('/^\s+|\s+$/', '', $value);
      }

      if (empty($value) || in_array($value, array('?','-','&mdash;'))) {
        unset($params[$key]);
      }
    }

    return $params;
  }


  /**
   * @param array $value
   * @return array
   */
  protected function parseLotPhotos(array $value)
  {
    $photos = array();
    foreach ($value as $item) {
      if ($item != 'http://f.mirkvartir.ru/original/00/00000000-0000-0000-0000-000000000000.jpg' && $item != 'http://files.mirkvartir.ru/files/images/zp.gif') {
        $photos[] = $item;
      }
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
    $city = ParseTools::getRegionName($this->lot_options['region_id']);

    $to_cut = array(
      '/\s*улица/i','/\s*проспект/i','/\s*площадь/i',
      '/\s*аллея/i','/\s*проезд/i','/\s*переулок/i',
      '/\s*бульвар/i','/\s*шоссе/i','/\s*район/i',
      '/\s*деревня/i','/\s*поселок/i','/\s*село/i',
      '/\s*город/i','/\s*станица/i'
    );

    $address1 = $city;
    $address2 = '';

    if ($this->lot_options['region_id'] == 77 || $this->lot_options['region_id'] == 78) {
      if (!empty($value['metro'])) {
        $address1 .= ', м. '.$value['metro'];
      } elseif (!empty($value['district'])) {
        if (mb_strpos($value['district'], ',', null, 'utf-8') !== false) {
          $value['district']= preg_replace('/[, ]*[^,]+ный[, ]*/isu', '', $value['district']);
          if (!empty($value['district'])) {
            $address1 .= ', р-н '.$value['district'];
          }
        } else {
          $address1 .= ', '.$value['district'];
        }
      }
      $tmp_addr = $value['address'];
      unset($value['metro'], $value['district'], $value['address']);

      if (($c = count($value)) > 3) {;
        for ($i=2; $i<$c; $i++) {
          $value[$i] = preg_replace('/ улица/isu', ' ул.', $value[$i]);
          $value[$i] = preg_replace('/ проспект/isu', ' просп.', $value[$i]);
          $value[$i] = preg_replace('/ площадь/isu', ' пл.', $value[$i]);
          $value[$i] = preg_replace('/ аллея/isu', ' алл.', $value[$i]);
          $value[$i] = preg_replace('/ проезд/isu', ' пр.', $value[$i]);
          $value[$i] = preg_replace('/ переулок/isu', ' пер.', $value[$i]);
          $value[$i] = preg_replace('/ бульвар/isu', ' бул.', $value[$i]);
          $value[$i] = preg_replace('/ шоссе/isu', ' ш.', $value[$i]);
          $value[$i] = preg_replace('/ район/isu', ' р-н.', $value[$i]);
          if (!empty($value[$i])) {
            $address2 .= $value[$i];

            if ($i != $c-1) $address2 .= ', ';
          }
        }
      } elseif (!empty($value[1])) {
        $value[1] = preg_replace($to_cut, '', $value[1]);
        $tmp_addr = preg_replace('/^.*'.$value[1].'[^,]*[, ]*/isu', '', $tmp_addr, -1, $count);
        $tmp_addr = preg_replace('/,\s*\d+[^,]+ от [^,]+/isu', '', $tmp_addr);
        $tmp_addr = preg_replace('/[,\s]*рядом с [^,]+/isu', '', $tmp_addr);
        if (mb_strlen($tmp_addr, 'utf-8') > 2 && $count) {
          $address2 .= $tmp_addr;
        }
      }
    } else {
      $tmp_addr = $value['address'];
      unset($value['metro'], $value['district'], $value['address']);

      if (($c = count($value)) > 3) {
        if (preg_match('/\s(район|р-н)/isu', $value[2])) {
          $address1 = $city.', '.$value[3];
          $i = 4;
        } else {
          $address1 = $city.', '.preg_replace('/ город/isu', '', $value[2]);
          $i = 3;
        }
        $address1 = preg_replace('/\s*[^\s]+ городского типа/isu', '', $address1);

        for ($i; $i<$c; $i++) {
          $value[$i] = preg_replace('/ улица/isu', ' ул.', $value[$i]);
          $value[$i] = preg_replace('/ проспект/isu', ' просп.', $value[$i]);
          $value[$i] = preg_replace('/ площадь/isu', ' пл.', $value[$i]);
          $value[$i] = preg_replace('/ аллея/isu', ' алл.', $value[$i]);
          $value[$i] = preg_replace('/ проезд/isu', ' пр.', $value[$i]);
          $value[$i] = preg_replace('/ переулок/isu', ' пер.', $value[$i]);
          $value[$i] = preg_replace('/ бульвар/isu', ' бул.', $value[$i]);
          $value[$i] = preg_replace('/ шоссе/isu', ' ш.', $value[$i]);
          $value[$i] = preg_replace('/ район/isu', ' р-н.', $value[$i]);
          if (!empty($value[$i])) {
            $address2 .= $value[$i];

            if ($i != $c-1) $address2 .= ', ';
          }
        }

        if ($c < 5) {
          $value[3] = preg_replace($to_cut, '', $value[3]);

          $tmp_addr = preg_replace('/^.*'.$value[3].'[^,]*[, ]*/isu', '', $tmp_addr, -1, $count);
          $tmp_addr = preg_replace('/,\s*\d+[^,]+ от [^,]+/isu', '', $tmp_addr);
          $tmp_addr = preg_replace('/[,\s]*рядом с [^,]+/isu', '', $tmp_addr);
          if (mb_strlen($tmp_addr, 'utf-8') > 2 && $count) {
            if (!empty($address2)) {
              $address2 .= ', '.$tmp_addr;
            } else {
              $address2 = $tmp_addr;
            }
          }
        }
      } elseif (!empty($value[2])) {
        $address1 = $city.', '.$value[2];

        $value[2] = preg_replace($to_cut, '', $value[2]);
        $tmp_addr = preg_replace('/^.*'.$value[2].'[^,]*[, ]*/isu', '', $tmp_addr, -1, $count);
        if (mb_strlen($tmp_addr, 'utf-8') > 2 && $count) {
          $address2 .= $tmp_addr;
        }
      }
    }

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
    $suffix = $this->lot_options['type'] == 'apartament-rent' ? 'arenda' : 'www';

    $this->parser->load($html);
    $container = $this->parser->getElementById('flats');
    if (!$container) return false;

    // first find lot links
    foreach ($container->find('.list_item') as $item) {
      $this->progress();
      if ($a = $item->find('a', 0)) {
        if (!$this->appendLotLink(sprintf('http://%s.mirkvartir.ru%s', $suffix, $a->href))) {
          return false;
        }
      }
      $item->clear();
    }

    // ... after extract page locations
    if ($paginator = $this->parser->getElementById('basecontent')->find('ul.pages', 0)) {
      foreach ($paginator->find('a') as $item) {
        $this->progress();
        if (ctype_digit($item->innertext) && $item->innertext > 1 && !empty($item->href) && $item->href != '#') {
          $link = sprintf('http://%s.mirkvartir.ru%s', $suffix, $item->href);
          if (!isset($this->pages[$item->innertext])) {
            $this->pages[$item->innertext] = $link;
          }
        }

        $item->clear();
      }
    }

    $this->parser->clear();
    unset($a, $paginator, $item, $html);

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

      case 'кирпич-монолит':
        return 'Монолитно-кирпичный';

      case 'деревянный':
      case 'д':
      case 'дер':
        return 'Дерево';

      case 'к':
      case 'кирп':
        return 'Кирпичный';

      case 'м':
      case 'мон':
      case 'монолит':
        return 'Монолитный';

      case 'п':
      case 'пан':
      case 'панель':
      case 'panel':
        return 'Панельный';

      case 'б':
      case 'блок':
        return 'Блочный';
    }

    return null;
  }
}