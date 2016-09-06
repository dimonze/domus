<?php

/**
 * Class for fetching http://vsenovostroyki.ru/ lots
 *
 * @author Grechko Aleksey
 */
class Fetcher_Vsenovostroyki extends BaseFetcher
{

  protected function importLot($url)
  {
    try {
      $html = $this->fetch($url, array(
        'strip_comments'        => true,
        'strip_html'            => true,
        //'strip_html_options'    => array('script'),
        'only_body'             => false,
        'use_proxy'             => $this->limit > 10,
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
    $container = $this->parser->find('div.main', 0);

    if (!is_object($container)) {
      ParseLogger::writeError($url, 'Some HTML shit happens...');
      return false;
    }

    if ($fragment = $container->find('div.price', 0)) {
      $data['params']['Мин. цена кв.м.'] = preg_replace('/&[^;]+;/', '', $fragment->plaintext); //cut off &sup2;
      $data['params']['Мин. цена кв.м.'] = preg_replace('/\D/', '', $data['params']['Мин. цена кв.м.']);
    }
    if (empty($data['params']['Мин. цена кв.м.']) || !($data['params']['Мин. цена кв.м.'] > 0)) {
      ParseLogger::writeError($url, ParseLogger::EMPTY_PRICE);
      return false;
    }

    if ($fragment = $container->find('ul.features', 0)) {
      foreach ($fragment->find('li') as $item) {
        $param_name = rtrim($item->children(0)->innertext, ':');
        switch ($param_name) {
          case 'Регион':  $pointer = &$data['title']['region'];   break;
          case 'Метро':   $pointer = &$data['title']['metro'];    break;
          case 'Адрес':   $pointer = &$data['title']['address'];  break;
          default:        $pointer = &$data['params'][$param_name];
        }
        $pointer = preg_replace('/\s*<strong>[^<]+<\/strong>\s*/i', '', $item->innertext);
        $pointer = preg_replace('/&[^;]+;/', '', $pointer);
        $pointer = trim($pointer);
      }
    }
    if (empty($data['title']['address'])) {
      ParseLogger::writeError($url, ParseLogger::EMPTY_ADDRESS);
      return false;
    }

    preg_match('/(ЖК\s*(?:«|")[^»"]+(?:»|"))/siu', $data['title']['address'], $matches);
    if (!empty($matches[1])) {
      $data['params']['Название ЖК'] = trim($matches[1]);
      $data['title']['address'] = preg_replace('/,*\s*ЖК\s*(?:«|")[^»"]+(?:»|")/u', '', $data['title']['address']);
    }
    elseif ($fragment = $container->find('div.right', 0)->first_child()) {
      preg_match('/(ЖК\s*(?:«|")[^»"]+(?:»|"))/siu', $fragment->innertext, $matches);
      if (!empty($matches[1])) {
        $data['params']['Название ЖК'] = trim($matches[1]);
      }
    }

    if ($fragment = $container->find('div.descr', 0)) {
      $data['description'] = trim($fragment->plaintext);
      $data['description'] = rtrim($data['description'], 'Подробное описание');
    }

    if ($fragment = $container->find('div.gallery', 0)) {
      foreach ($fragment->children(0)->find('a') as $item) {
        $data['photos'][] = 'http://vsenovostroyki.ru'.$item->href;
      }
    }

    /*if ($fragment = $this->parser->getElementById('map')) { there is not a lot coordinates!:(
      $item = $fragment->next_sibling();
      for ($i=0; $i<5; $i++) {
        if (is_null($item)) break;
        if ($item->tag == 'script') {
          preg_match('/center\: \[([\d.]+),\s*([\d.]+)\]/', $item->innertext, $matches);
          if (!empty($matches[1]) && !empty($matches[2])) {
            $data['latitude']  = floatval($matches[1]);
            $data['longitude'] = floatval($matches[2]);
            break;
          }
        }
        $item = $item->next_sibling();
      }
    }*/

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
    foreach ($params as $key => &$value) {
      $this->progress();

      if (empty($value) || in_array($key, array(
          'Адрес',
          'Метро',
          'Регион',
          'Кол-во машиномест',
        ))) {
        continue;
      }

      switch ($key) {
        case 'Удаленность от метро':  $key = 'Удаленность от метро/ж.д. станции'; break;
        case 'Класс объекта':         $key = 'Класс дома'; break;
      }

      if ($key == 'Этажность') {
        if (!ctype_digit(trim($value))) continue;
        $value = preg_replace('/\D+/', '', $value);
      }
      elseif ($key == 'Площадь квартир') {
        $parts = preg_split('/-|до/', $value, 2);
        if (count($parts) == 1) {
          $checked['Мин. площадь'] = trim(str_replace(',', '.', preg_replace('/[^\d.,]/', '', $parts[0])), '.');
        }
        elseif (count($parts) == 2) {
          $checked['Мин. площадь']  = trim(str_replace(',', '.', preg_replace('/[^\d.,]/', '', $parts[0])), '.');
          $checked['Макс. площадь'] = trim(str_replace(',', '.', preg_replace('/[^\d.,]/', '', $parts[1])), '.');
        }
        continue;
      }
      elseif ($key == 'Срок ввода') {
        preg_match('/(\d{4})/', $value, $matches);
        if (!empty($matches[1]) && $matches[1] > date('Y')-2 && $matches[1] < date('Y')+3) {
          $key = 'Год сдачи';
          $value = $matches[1];
        }
        else continue;
      }
      elseif ($key == 'Мин. цена кв.м.') {
        $value = 'RUR'.$value;
      }


      if (is_string($value)) {
        $value = preg_replace('/^\s+|\s+$/', '', $value);
      }

      $checked[$key] = $value;
    }

    return $checked;
  }


  /**
   * Parse address row
   * @param string $value
   * @return array $address
   */
  protected function parseLotAddress($value)
  {
    $data = array(
      'address1' => '',
      'address2' => '',
    );

    if ($this->lot_options['region_id'] == 77 || $this->lot_options['region_id'] == 78) {
      $data['address1'] = ParseTools::getRegionName($this->lot_options['region_id']);
      if (!empty($value['metro'])) {
        preg_match('/^([^,]+)(?:,|$)/', strip_tags($value['metro']), $matches);
        if (!empty($matches[1])) {
          $data['address1'] .= ', м. '.trim($matches[1]);
        }
      }
    }
    else {
      $data['address1'] = strip_tags($value['region']);
      $data['address1'] = preg_replace('/\s+/', ' ', $data['address1']);
      $data['address1'] = trim($data['address1']);
    }

    $parts = explode(',', $data['address1']);
    $parts = array_map('trim', $parts);
    foreach ($parts as $p) {
      $p = preg_replace('/\s+/', '\s+', $p);
      $p = preg_replace('/([.()])/u', '\\\$1', $p);
      $value['address'] = preg_replace('/^.*'.$p.'(?:[^,]*(?:,|$))/isu', '', $value['address']);
    }

    if (!empty($value['address'])) {
      $data['address2'] = trim($value['address']);
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
    $container = $this->parser->find('section.offers', 0);
    if (!$container) return false;

    // first find lot links
    foreach ($container->find('article') as $item) {
      $this->progress();
      if ($a = $item->find('a', 0)) {
        $link = 'http://vsenovostroyki.ru'.mb_strtolower($a->href, 'utf-8');
        $link = preg_replace('/([^\/:?&=])/iue', "urlencode('$1')", $link);
        if (!$this->appendLotLink($link)) {
          return false;
        }
      }
      $item->clear();
    }

    // ... after extract page locations
    if ($paginator = $this->parser->find('nav.pagination', 0)) {
      foreach ($paginator->find('a') as $item) {
        $this->progress();
        if (ctype_digit($item->innertext) && $item->innertext > 1 && !empty($item->href) && $item->href != '#') {
          $link = preg_replace('/(&|\?)p=\d+/', '$1p='.$item->innertext, $this->pages[0]);//because of shitty paging on web-site
          //$link = 'http://vsenovostroyki.ru'.$item->href;
          //$link = preg_replace('/([^\/:?&=])/iue', "urlencode('$1')", $link);
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


  protected function translateParamValue($value, $field_id = null)
  {
    $value = mb_strtolower($value, 'utf-8');

    if ($field_id == 77) {//Класс дома
      foreach (array('эконом','бизнес','премиум') as $v) {
        if (mb_stripos($value, $v, null, 'utf-8') !== false) {
          return mb_strtoupper(mb_substr($v, 0, 1, 'utf-8'), 'utf-8').mb_substr($v, 1, mb_strlen($v, 'utf-8'), 'utf-8');
        }
      }

      if (mb_stripos($value, 'элит', null, 'utf-8') !== false ||
          mb_stripos($value, 'преми', null, 'utf-8') !== false) {
        return 'Премиум';
      }

      $value = mb_strtoupper(mb_substr($v, 0, 1, 'utf-8'), 'utf-8').mb_substr($v, 1, mb_strlen($v, 'utf-8'), 'utf-8');
    }
    elseif ($field_id == 74) {//Состояние строительства
      if (mb_stripos($value, 'фундамент', null, 'utf-8') !== false ||
          mb_stripos($value, 'возведение', null, 'utf-8') !== false ||
          mb_stripos($value, 'идет строител', null, 'utf-8') !== false) {
        return 'строится';
      }
      elseif (mb_stripos($value, 'отдел', null, 'utf-8') !== false) {
        return 'отделка';
      }
      elseif (mb_stripos($value, 'закончен', null, 'utf-8') !== false) {
        return 'построен';
      }
      elseif (mb_stripos($value, 'эксплуатац', null, 'utf-8') !== false) {
        return 'сдан';
      }
      elseif (mb_stripos($value, 'площадк', null, 'utf-8') !== false) {
        return 'площадка';
      }


      foreach (array('проект','площадка','котлован','строится','отделка','построен','сдан') as $v) {
        if (mb_stripos($value, $v, null, 'utf-8') !== false) {
          return $v;
        }
      }
    }

    return $value;
  }
}