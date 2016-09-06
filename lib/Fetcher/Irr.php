<?php

/**
 * Class for fetching http://irr.ru/real-estate/ lots
 *
 * @author Grechko Aleksey
 */
class Fetcher_Irr extends BaseFetcher
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
          'strip_comments'        => true,
          'strip_html'            => true,
          'strip_html_options'    => array('input'),
          'only_body'             => false,
          'check_https_redirect'  => true,
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
    $container = $this->parser->getElementById('content')->find('div.content_left', 0);


    if ($fragment = $container->find('div.credit_cost', 0)) {
      if ($this->lot_options['type'] == 'commercial-rent') {
        if (mb_strpos($fragment->innertext, '/месяц')) {
          $data['price'] = preg_replace('/\D/', '', $fragment->innertext);
        }
        elseif (mb_strpos($fragment->innertext, '/год')) {
          $data['price'] = preg_replace('/\D/', '', $fragment->innertext);
          $data['price'] = round(intval($data['price'])/12);
        }
      }
      else {
        $data['price'] = preg_replace('/\D/', '', $fragment->innertext);
      }
    }
    if (empty($data['price']) || !($data['price'] > 0)) {
      ParseLogger::writeError($url, ParseLogger::EMPTY_PRICE);
      return false;
    }


    foreach ($container->find('ul.form_info', 0)->children() as $item) {
      if ($item->tag != 'li') continue;

      switch ($item->children(0)->innertext) {
        case 'Продавец:':
          preg_match('/^([^<]+)/u', $item->children(1)->innertext, $matches);
          if (!empty($matches[1]) && mb_strpos($matches[1], 'Частное объявление') === false) {
            $data['organization_contact_name'] = trim(preg_replace('/[\s\-—]*$/u', '', $matches[1]));
          }
          break;

        case 'Контактное лицо:':
          $data['organization_contact_name'] = trim($item->children(1)->plaintext);
          break;
      }
    }

    if (!empty($data['organization_contact_name']) && !preg_match('/[а-я]/iu', $data['organization_contact_name'])) {
      unset($data['organization_contact_name']);
    }

    if ($fragment = $container->getElementById('allphones')) {
      $data['organization_contact_phone'] = base64_decode($fragment->getAttribute('value'));
    }

    $this->progress();

    foreach ($container->find('span.title') as $item) {
      if ($item->innertext == 'Описание товара') {
        $fragment = $item->next_sibling();
        if (!is_null($fragment) && $fragment->tag == 'p' && $fragment->getAttribute('class') == 'text') {
          $data['description'] = $fragment->innertext;
        }
      }
      elseif ($item->innertext == 'Расположение') {
        if ($fragment = $item->next_sibling()) {
          do {
            if ($fragment->tag == 'p' && $fragment->getAttribute('class') == 'text') {
              if ($fragment->find('span.metro', 0)) {
                $data['title']['metro'] = trim($fragment->find('span.metro', 0)->plaintext);
              }
              else {
                if (empty($data['title']['address'])) {
                  $data['title']['address'] = trim($fragment->plaintext);
                }
                else {
                  $data['title']['address'] .= ', '.trim($fragment->plaintext);
                }
              }
            }
          } while ($fragment = $fragment->next_sibling());
        }
        break;
      }
    }
    if (empty($data['title']['address'])) {
      ParseLogger::writeError($url, ParseLogger::EMPTY_ADDRESS);
      return false;
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

    if ($fragment = $container->find('div.grey_info', 0)) {
      if ($item = $fragment->find('span.data', 0)) {
        $data['date'] = $item->innertext;
      }
    }

    foreach ($container->find('div.slide') as $fragment) {
      foreach ($fragment->children as $item) {
        if ($item->tag == 'img' && $item->getAttribute('class') != 'add_premium') {
          $data['photos'][] = $item->getAttribute('src');
        }
      }
    }

    foreach ($container->find('ul.form_info_short') as $fragment) {
      foreach ($fragment->children() as $item) {
        $param_name = trim($item->children(0)->innertext);
        $param_name = preg_replace('/\:$/u', '', $param_name);
        $data['params'][$param_name] = trim($item->children(1)->innertext);
      }
    }
    if (strpos($this->lot_options['type'], 'commercial') !== false) {
      if (isset($data['params']['Тип здания'])) {
        $data['params']['Тип недвижимости'] = ParseTools::matchCommercialType($data['params']['Тип здания']);
      }
      elseif (($fragment = $container->find('h1.title3', 0))) {
        $data['params']['Тип недвижимости'] = ParseTools::matchCommercialType($fragment->plaintext);
      }
    }
    unset($data['params']['&nbsp;']);


    if ($item = $container->getElementById('geo_x')) {
      $data['latitude'] = $item->getAttribute('value');
    }
    if ($item = $container->getElementById('geo_y')) {
      $data['longitude'] = $item->getAttribute('value');
    }

    ParseTools::preg_clear_cache();
    if (is_object($fragment)) $fragment->clear();
    $container->clear();
    $this->parser->clear();
    unset($item, $fragment, $container, $html);

    $this->progress();
    $data = $this->parseLotData($data);

    if ($this->lot_options['type'] == 'commercial-rent') {
      if (isset($data['params'][46])) {
        $data['params'][53] = ceil(($data['price'] / $data['params'][46])*12);
      }
    }

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

      if (in_array($key, array('Регион',
                              'Район города',
                              'Микрорайон',
                              'Строение',
                              'АО',
                              'До метро, минут',
                              'До метро',
                              'До метро, мин/пеш',
                              'Как?',
                              'Улица',
                              'Дом',
                              'Направление',
                              'Удаленность',
                              'Соседей в квартире',
                              'Приватизированная квартира',
                              'В собственности более 3-х лет',
                              'Вид из окна',
                              'Возможна ипотека',
                              'Межэтажные и межкомнатные перекрытия',
                              'Количество телефонных линий',
                              'В городе',
                              'Период аренды',
                              'Серия здания',
                              'Комиссия',
                              'Отдельный вход',
                              '1-я линия',
                              ))) {
        continue;
      }

      switch ($key) {
        case 'Кол-во комнат в доме':
        case 'Комнат в квартире': $key = 'Количество комнат'; break;
        case 'Площадь строения':  $key = 'Общая площадь'; break;
        case 'Населенный пункт':  $key = 'Тип поселка'; break;
        case 'Площадь участка в сотках':  $key = 'Площадь участка'; break;
        case 'Класс': $key = 'Класс офиса'; break;
        case 'Система отопления':     $key = 'Отопление'; break;
        case 'Система водоснабжения': $key = 'Водопровод'; break;
        case 'Система канализации':   $key = 'Канализация'; break;
        case 'Год постройки/сдачи':   $key = 'Год постройки'; break;
        case 'Этажность дома':
        case 'Этажей':
        case 'Количество этажей':
        case 'Этажей в здании':       $key = 'Этажность'; break;
        case 'Комнат продается':
        case 'Комнат сдаётся':
        case 'Всего комнат в квартире':
        case 'Комнат в квартире':     $key = 'Количество комнат'; break;
      }

      if (in_array($key, array('Этаж','Этажность','Год постройки','Количество комнат'))) {
        $value = preg_replace('/\D+/', '', $value);
      }

      if (in_array($key, array('Тип дома','Материал стен'))) {
        $key = mb_strpos($this->lot_options['type'], 'house') === false ? 'Тип здания' : 'Тип дома';
      }
      if ($key == 'Количество комнат' && mb_strpos($this->lot_options['type'], 'apartament') !== false) {
        $key = 'Тип предложения';
        $value = $value.ending($value, '', '-х', '-ти', '-ми').' комнатная квартира';
      }
      if ($key == 'Тип предложения' && mb_strpos($this->lot_options['type'], 'commercial') !== false) {
        $key = 'Тип недвижимости';
      }

      if (in_array($key, array('Общая площадь','Жилая площадь','Площадь кухни'))) {
        $key = str_replace(' кв.м', '', $key);
        $value = floatval($value);
      }
      elseif ($key == 'Площадь участка') {
        $key = str_replace(' сот', '', $key);
        $value = floatval($value);
      }

      if ($key == 'Общая площадь' && mb_strpos($this->lot_options['type'], 'house') !== false) {
        $key = 'Площадь дома';
      }

      if ($key == 'Ремонт') {
        switch ($this->lot_options['type']) {
          case 'apartament-sale': break;
          case 'apartament-rent': $key = 'Состояние/ремонт';  break;
          case 'commercial-rent': $key = 'Состояние, отделка, готовность';  break;
          case 'commercial-sale': $key = 'Состояние, отделка';  break;
          case 'house-rent':      $key = 'Ремонт/состояние';  break;
          case 'house-sale':      $key = 'Ремонт/состояние';  break;
        }
      }

      if ($key == 'Отопление') {
        if (mb_stripos($value, 'центр') !== false) {
          $value = 'централизованное';
        }
        elseif (mb_stripos($value, 'газ') !== false) {
          $value = 'газовое';
        }
        elseif (mb_stripos($value, 'электр') !== false) {
          $value = 'электрическое';
        }
        elseif (mb_stripos($value, 'печь') !== false || mb_stripos($value, 'камин') !== false) {
          $value = 'печь или камин';
        }
      }
      elseif ($key == 'Водопровод') {
        if (mb_stripos($value, 'центр') !== false) {
          $value = 'центральный';
        }
        elseif (mb_stripos($value, 'скваж') !== false) {
          $value = 'скважина';
        }
        elseif (mb_stripos($value, 'колоде') !== false) {
          $value = 'колодец';
        }
      }
      elseif ($key == 'Канализация') {
        if (mb_stripos($value, 'центр') !== false) {
          $value = 'центральная';
        }
        elseif (mb_stripos($value, 'септи') !== false) {
          $value = 'септик';
        }
      }

      if ($key == 'Санузел') {
        if (mb_stripos($value, 'совм') !== false) {
          $value = 'совмещенный';
        }
        elseif (mb_stripos($value, 'разд') !== false) {
          $value = 'раздельный';
        }
      }

      if ($key == 'Балкон' || $key == 'Балкон/Лоджия') {
        $key = 'Балкон/лоджия';
        $value = mb_strtolower($value, 'utf-8');
        if (in_array($value, array('+','да','есть','<span class="nicecheck"><input type="checkbox" disabled checked="checked"></span>'))) {
          $value = 'балкон';
        }
      }
      elseif ($key == 'Бытовая техника') {
        $key = 'Оборудование/бытовая техника';
        $value = mb_strtolower($value);
        if (in_array($value, array('+','да','есть','<span class="nicecheck"><input type="checkbox" disabled checked="checked"></span>'))) {
          $value = 'да';
        }
      }
      elseif ($key == 'Отапливаемый') {
        $key = 'Отопление';
        $value = mb_strtolower($value);
        if (in_array($value, array('+','да','есть','<span class="nicecheck"><input type="checkbox" disabled checked="checked"></span>'))) {
          $value = 'да';
        }
      }
      elseif ($key == 'Электричество (подведено)') {
        $key = 'Электричество';
        $value = mb_strtolower($value);
        if (in_array($value, array('+','да','есть','<span class="nicecheck"><input type="checkbox" disabled checked="checked"></span>'))) {
          $value = 'да';
        }
      }
      elseif (in_array($key, array('Электричество','Водопровод','Канализация','Мебель'))) {
        $value = mb_strtolower($value);
        if (in_array($value, array('+','да','есть','<span class="nicecheck"><input type="checkbox" disabled checked="checked"></span>'))) {
          $value = 'есть';
        }
      }
      elseif (in_array($key, array('Гараж','Баня'))) {
        $key = mb_strtolower($key, 'utf-8');

        $value = mb_strtolower($value, 'utf-8');
        if (in_array($value, array('+','да','есть','<span class="nicecheck"><input type="checkbox" disabled checked="checked"></span>'))) {
          if (!empty($checked['Доп. строения'])) {
            $checked['Доп. строения'] .= ', '.$key;
          } else {
            $checked['Доп. строения'] = $key;
          }
        }
        continue;
      }

      if (in_array($key, array('Мусоропровод',
                               'Есть ли лифт',
                               'Лифты в здании',
                               'Городской телефон',
                               'Интернет',
                               'Телефон',
                               'Парковка',
                               'Лес',
                               'Водоем',
                               'Возможно ПМЖ',
                               'Охрана',
                               'Охрана здания',
                               'Газ в доме',
                              ))) {
        switch ($key) {
          case 'Мусоропровод':
          case 'Парковка':
          case 'Интернет':
          case 'Телефон':
          case 'Лес':
          case 'Водоем':
          case 'Охрана':
            $key = mb_strtolower($key, 'utf-8');
            break;

          case 'Газ в доме':
            $key = 'газ';
            break;

          case 'Лифты в здании':
          case 'Есть ли лифт':
            $key = 'лифт';
            break;

          case 'Городской телефон':
            $key = 'телефон';
            break;

          case 'Возможно ПМЖ':
            $key = 'пмж';
            break;

          case 'Охрана здания':
            $key = 'охрана';
            break;
        }

        $value = mb_strtolower($value, 'utf-8');
        if (in_array($value, array('+','да','есть','<span class="nicecheck"><input type="checkbox" disabled checked="checked"></span>'))) {
          if (!empty($checked['Детали'])) {
            $checked['Детали'] .= ', '.$key;
          } else {
            $checked['Детали'] = $key;
          }
        }
        continue;
      }

      if (preg_match('/^[\d\s.,]+$/mi', $value)) {
        $value = str_replace(',', '.', preg_replace('/[^\d.,]+[^\d]/', '', $value));
      } elseif (is_string($value)) {
        $value = preg_replace('/^\s+|\s+$/', '', $value);
      }

      if (empty($value) || $value == 'Возможен' || $value == 'Возможно подведение') {
        unset($params[$key]);
        continue;
      }

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
    return str_replace(array_keys($this->months), array_values($this->months), $value);
  }


  /**
   * Parse address row
   * @param string $value
   * @return array $address
   */
  protected function parseLotAddress($value)
  {
    if ($this->lot_options['region_id'] == 77 && mb_stripos($value['address'], 'Московская') === 0) {
      $this->lot_options['region_id'] = 50;
    }
    elseif ($this->lot_options['region_id'] == 78 && mb_stripos($value['address'], 'Ленинградская') === 0) {
      $this->lot_options['region_id'] = 47;
    }
    elseif ($this->lot_options['region_id'] == 50 && mb_stripos($value['address'], 'Москва') === 0) {
      $this->lot_options['region_id'] = 77;
    }
    elseif ($this->lot_options['region_id'] == 47 && mb_stripos($value['address'], 'Санкт-Петербург') === 0) {
      $this->lot_options['region_id'] = 78;
    }

    $city = ParseTools::getRegionName($this->lot_options['region_id']);

    $address1 = $city;
    $address2 = '';

    $parts = explode(',', $value['address']);
    foreach ($parts as $i => &$p) {
      $p = trim($p);
      similar_text($p, $city, $perc);
      if ($perc > 80) unset($parts[$i]);
    }

    if ($this->lot_options['region_id'] == 77 || $this->lot_options['region_id'] == 78) {
      if (!empty($value['metro'])) {
        $address1 .= ', м. '.$value['metro'];
      }
    }
    elseif (count($parts))  {
      $address1 .= ', '.array_shift($parts);
    }

    if (!empty($parts)) {
      $address2 = implode(', ', $parts);
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
    $suffix = mb_substr($this->pages[0], 0, mb_stripos($this->pages[0], '.ru/', null, 'utf-8')+3, 'utf-8');

    $this->parser->load($html);
    $container = $this->parser->getElementById('content')->find('div.adds_list', 0);
    if (!$container) return false;

    // first find lot links
    foreach ($container->find('div.adds_cont', 0)->children() as $item) {
      $this->progress();
      if ($item->tag != 'div') continue;
      if (mb_strpos($item->getAttribute('class'), 'add_head') !== false && $item->innertext == 'Предложения из ближайших регионов') return false;
      if (mb_strpos($item->getAttribute('class'), 'add_list') === false) continue;

      if ($a = $item->find('a.add_title', 0)) {
        $link = mb_strtolower($a->href, 'utf-8');
        $link = preg_replace('/\/[a-z\d\-_]*(advert\d+.html)$/i', '/$1', $link, -1, $c);
        if (!$c) continue;
        if (!$this->appendLotLink($link)) {
          return false;
        }
      }
      $item->clear();
    }

    // ... after extract page locations
    if ($paginator = $container->find('div.adds_paging', 0)) {
      foreach ($paginator->find('a') as $item) {
        $this->progress();
        if (ctype_digit($item->innertext) && $item->innertext > 1 && !empty($item->href) && $item->href != '#') {
          $link = $suffix.$item->href;
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
    switch (mb_strtolower($value, 'utf-8')) {
      case 'да':
        return 'есть';

      case 'есть':
      case 'нет':
      case 'без ремонта':
        return $value;

      case 'сталинка':
        return false;

      case 'кирпич-монолит':
        return 'Монолитно-кирпичный';

      case 'кирпич':
        return 'Кирпичный';

      case 'панель':
        return 'Панельный';

      case 'монолит':
        return 'Монолитный';

      case 'деревянный':
      case 'брус':
        return 'Дерево';

      case 'блоки':
        return 'Блочный';

      case 'газопровод':
        return 'магистральный';

      case 'протянут по границе':
        return 'по границе';

      case 'требует капитального ремонта':
        return 'требует кап ремонта';
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