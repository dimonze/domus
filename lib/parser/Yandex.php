<?php

class Parser_Yandex extends Parser
{
  protected
    $current_content, $current_dom,
    $user_id = 23639,

    $lat_lng_boxes = array(),
    $current_lat_lng_box = null,
    $extended_query = array(
      77 => array(
        'box'  => array(55.55724520829424, 37.891324684023864, 55.92176375370614, 37.32003562152388),
        'text' => 'Россия, Москва'
      ),
      78 => array(
        'box'  => array(59.819289270410366, 30.5498498864472, 60.064576761009505, 30.129622835665945),
        'text' => 'Россия, Санкт-Петербург'
      ),
    );

  public function __construct($type, $region_id, $fetcher_options = array())
  {
    parent::__construct($type, $region_id, $fetcher_options);
    ProjectConfiguration::registerSimpleHtmlDom();
    if (isset($this->extended_query[$region_id])) {
      $this->initLatLngBoxes($this->extended_query[$region_id]);
    }
  }

  /**
   * Получить список объявлений с базовыми параметрами
   * @param integer $page
   * @return array
   */
  public function getItems($page = 1)
  {
    $this->current_content = $this->fetcher->fetch($this->buildSearchUrl($page), array(
      'strip_comments' => true,
      'strip_html'     => true,
      'cleanup'        => function($html) {
        $regex = '/<td class="b-layout__table-left">(.+)<\/td>\s*<td class="b-layout__table-right/isU';
        if (preg_match($regex, $html, $matches)) {
          return $matches[1];
        }
      }
    ));

    $this->current_dom = new SimpleHTMLDOM();
    $this->current_dom->load($this->current_content);

    $items = array();
    foreach ($this->current_dom->find('div.b-serp-item') as $node) {
      if ($item = $this->parseListItem($node)) {
        // check address if search was done by coords
        if (!$this->current_lat_lng_box || 0 === strpos($item['address'], $this->extended_query[$this->region_id]['text'])) {
          $items[] = $item;
        }
      }
    }

    return $items;
  }

  /**
   * Существует ли следующая страница поисковой выдачи?
   * @return boolean
   */
  public function hasNextPage()
  {
    if (!$this->current_dom) {
      return false;
    }

    $cpage = $this->current_dom->find('b.b-pager__current', 0);
    return $cpage && $cpage->nextSibling();
  }

  /**
   * Распарсить объявление учитывая базовые параметры
   * @param array $data
   * @return integer|boolean Lot->id or false
   */
  public function parseItem(array $data)
  {
    $data['images'] = $data['phones'] = array();

    if (!($data['url'] = $this->getSourceUrl($data['url']))) {
      return false;
    }
    $host = preg_replace('/^(.+\.)?([a-z0-9-]+)\.[a-z]+$/i', '$2', parse_url($data['url'], PHP_URL_HOST));

    // TMP
    $log = sprintf("%d\t%s\t%s\t%s\t%s\n", $this->region_id, $this->type, $host, parse_url($data['url'], PHP_URL_HOST), $data['url']);
    file_put_contents(sfConfig::get('sf_log_dir') . '/yandex_source', $log, FILE_APPEND);
    // \TMP

    switch ($host) {
      case 'cian':
        $this->addCianData($data);
        break;

      case 'eip':
        $this->addEipData($data);
        break;

      case 'v-nedv':
        $this->addVNedvData($data);
        break;

      case 'mlsn':
        $this->addMlsnData($data);
        break;

      case 'volga-info':
        $this->addVolgaInfoData($data);
        break;

      default:
        return false;
    }

    if (!$data['phones']) {
      return false;
    }


    $lot_data = $data;
    $parsers = array(
      'parseItemTitle'       => array($data['title']),
      'parseAddress'         => array($data['address'], $data['address_info']),
      'parseItemDescription' => array($data['description']),
    );
    foreach ($parsers as $parser => $param) {
      if (false === ($_data = call_user_func_array(array($this, $parser), $param))) {
        return false;
      }
      $lot_data = array_merge($lot_data, $_data);
    }

    return $lot_data;
  }


  /**
   * Урл выдачи результатов
   * @param integer $page
   * @return string
   */
  private function buildSearchUrl($page)
  {
    switch ($this->type) {
      case 'apartament-sale':
        $type = 'SELL';
        $category = 'APARTMENT';
        break;

      case 'apartament-rent':
        $type = 'RENT';
        $category = 'APARTMENT';
        break;

      case 'house-sale':
        $type = 'SELL';
        $category = 'HOUSE';
        break;

      case 'house-rent':
        $type = 'RENT';
        $category = 'HOUSE';
        break;

      default:
        throw new Exception(sprintf('Unable to build url for type "%s"', $this->type));
    }

    $params = array(
      'type'             => $type,
      'category'         => $category,
      'currency'         => 'RUR',
      'priceType'        => 'PER_OFFER',
      'selectedRegionId' => $this->convertRegionId($this->region_id),
      'page'             => $page - 1,
      'sort'             => 'DATE_DESC',
    );

    if ('RENT' == $type) {
      $params['rentTime'] = 'LARGE';
    }

    if ($this->current_lat_lng_box) {
      list($params['ltLatitude'], $params['ltLongitude'], $params['rbLatitude'], $params['rbLongitude'])
        = $this->current_lat_lng_box;
    }

    return 'http://realty.yandex.ru/search.xml?' . http_build_query($params);
  }

  public function skipCurrentQuery()
  {
    if (!$this->hasNextQuery()) {
      return false;
    }

    $this->current_lat_lng_box = array_shift($this->lat_lng_boxes);
    return true;
  }

  public function hasNextQuery()
  {
    return !empty($this->lat_lng_boxes);
  }

  private function initLatLngBoxes(array $data)
  {
    // ~ 0.075 x 0.1
    for ($lat = $data['box'][0]; $lat < $data['box'][2]; $lat += 0.055) {
      for ($lng = $data['box'][3]; $lng < $data['box'][1]; $lng += 0.073) {
        $this->lat_lng_boxes[] = array(
          $lat,         // left top latitude
          $lng + 0.073, // left top longitude
          $lat + 0.055, // right bottom latitude
          $lng,         // right bottom longitude
        );
      }
    }
    $this->skipCurrentQuery();
  }


  /**
   * Парсинг базовых параметров объявления из списка
   * @param SimpleHTMLDOMNode $node
   * @return array
   */
  private function parseListItem(SimpleHTMLDOMNode $node)
  {
    $address_info = strip_tags(@$node->find('.b-serp-item__address-text div', 0)->innertext);
    $address = strip_tags(@$node->find('.b-serp-item__address-text', 0)->innertext);
    if (!empty($address_info)) {
      $address = str_replace($address_info, '', $address);
    }

    if ($owner = $node->find('.b-serp-item__owner', 0)) {
      $date = $this->parseDate($owner->innertext);
    }
    else {
      $date = false;
    }

    if (!$node->find('.b-serp-item__header a', 0)) {
      return false;
    }

    $item = array(
      'url'          => htmlspecialchars_decode($node->find('.b-serp-item__header a', 0)->href),
      'title'        => strip_tags($node->find('.b-serp-item__header', 0)->innertext),
      'price'        => (int) preg_replace('/\D+/', '', @$node->find('.b-serp-item__amount', 0)->innertext),
      'address'      => $address,
      'address_info' => strip_tags(@$node->find('.b-serp-item__address-text div', 0)->innertext),
      'description'  => @$node->find('.b-serp-item__about', 0)->innertext,
      'source'       => htmlspecialchars_decode($node->find('.b-serp-item__header a', 0)->href),
      'date'         => $date,
    );

    return $item;
  }

  private function parseDate($date)
  {
    if (preg_match('/размещено(.*)<br>/', $date, $matches)) {
      @list($created_at, $updated_at) = explode(',', $matches[1]);
      return trim($created_at);
    }

    return false;
  }

  /**
   * Конвертация Region.id -> Yandex.region_id
   * @param integer $id
   * @return integer
   */
  private function convertRegionId($id)
  {
    $regions = array(
      77 => 213,
      50 => 1,
      78 => 2,
      47 => 10174,
      4  => 11235,
      28 => 11375,
      29 => 10842,
      30 => 10946,
      31 => 10645,
      32 => 10650,
      33 => 10658,
      34 => 10950,
      35 => 10853,
      36 => 10672,
      79 => 10243,
      75 => 21949,
      37 => 10687,
      38 => 11266,
      39 => 10857,
      40 => 10693,
      41 => 11398,
      9  => 11020,
      42 => 11282,
      43 => 11070,
      44 => 10699,
      23 => 10995,
      24 => 11309,
      45 => 11158,
      46 => 10705,
      48 => 10712,
      49 => 11403,
      51 => 10897,
      83 => 10176,
      52 => 11079,
      53 => 10904,
      54 => 11316,
      55 => 11318,
      56 => 11084,
      57 => 10772,
      58 => 11095,
      59 => 11108,
      25 => 11409,
      60 => 10926,
      1  => 11004,
      4  => 10231,
      2  => 11111,
      3  => 11330,
      5  => 11010,
      6  => 11012,
      7  => 11013,
      8  => 11015,
      10 => 10933,
      11 => 10939,
      12 => 11077,
      13 => 11117,
      14 => 11443,
      15 => 11021,
      16 => 11119,
      17 => 10233,
      19 => 11340,
      61 => 11029,
      62 => 10776,
      63 => 11131,
      64 => 11146,
      65 => 11450,
      66 => 11162,
      67 => 10795,
      26 => 11069,
      68 => 10802,
      69 => 10819,
      70 => 11353,
      71 => 10832,
      72 => 11176,
      18 => 11148,
      73 => 11153,
      27 => 11457,
      86 => 11193,
      74 => 11225,
      20 => 11024,
      21 => 11156,
      87 => 10251,
      89 => 11232,
      76 => 10841,
    );

    if (isset($regions[$id])) {
      return $regions[$id];
    }
    else {
      throw new Exception(sprintf('Unknown region_id: %s', $id));
    }
  }


  /**
   * Переход по редиректу яндекса
   * @param string $url
   * @return mixed string|null
   */
  private function getSourceUrl($url)
  {
    if (preg_match('/.*yandex.*/', $url)) {
      $ch = $this->fetcher->getCurl($url);
      curl_setopt_array($ch, array(
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER         => true,
      ));
      $response = curl_exec($ch);

      if (preg_match('/^Location: (.+)$/m', $response, $matches)){
        $url = preg_replace('/fromRealty=[^&]+/', '', trim($matches[1]));
        $url = preg_replace('/[?&]+$/', '', $url);
      }
    }

    return preg_replace(
      '/(&|\?)utm_source=realty\.yandex\.ru&utm_campaign=realty\.yandex\.ru&utm_term=\w+/',
      '', $url
    );
  }

  private function addCianData(array &$data)
  {
    $dom = new SimpleHTMLDOM();
    $dom->load($this->fetcher->fetch($data['url'], array(
      'track_referrer' => false,
      'strip_comments' => true,
      'strip_html'     => true,
    )));

    // Ищем фото
    foreach ($dom->find('.object_descr_images_w img') as $img) {
      $data['images'][] = $img->src;
    }

    // Выбираем телефоны
    if ($node = $dom->find('.object_descr_phones strong', 0)) {
      foreach (preg_split('/\s*[;,]\s*/', $node->innertext) as $phone) {
        if ($parsed = Toolkit::unformatPhoneNumber($phone, $this->region_id)) {
          $data['phones'][] = Toolkit::formatPhoneNumber(
            $parsed['country'], $parsed['area'], $parsed['number']
          );
        }
      }
    }
  }

  private function addEipData(array &$data)
  {
    $dom = new SimpleHTMLDOM();
    $dom->load($this->fetcher->fetch($data['url'], array(
      'track_referrer' => false,
      'strip_comments' => true,
      'strip_html'     => true,
    )));


    // Выбираем телефоны
    foreach ($dom->find('.ObjectBaseTbl td') as $node) {
      if (false !== mb_strpos($node->innertext, 'Контактная информация')) {
        $text = preg_split('/<br ?\/?>/i', $node->nextSibling()->innertext);
        if ($parsed = Toolkit::unformatPhoneNumber(array_pop($text), $this->region_id)) {
          $data['phones'][] = Toolkit::formatPhoneNumber(
            $parsed['country'], $parsed['area'], $parsed['number']
          );
        }
        break;
      }
    }
  }

  private function addVNedvData(array &$data)
  {
    $dom = new SimpleHTMLDOM();
    $dom->load($this->fetcher->fetch($data['url'], array(
      'track_referrer' => false,
      'strip_comments' => true,
      'strip_html'     => true,
    )));


    // Ищем фото
    foreach ($dom->find('.advert .thumb li a') as $link) {
      $data['images'][] = 'http://v-nedv.ru' . $link->href;
    }

    // Выбираем телефоны
    foreach ($dom->find('.contact td') as $node) {
      foreach (preg_split('/\s*[,;]\s*/', $node) as $part) {
        if ($parsed = Toolkit::unformatPhoneNumber($part, $this->region_id)) {
          $data['phones'][] = Toolkit::formatPhoneNumber(
            $parsed['country'], $parsed['area'], $parsed['number']
          );
        }
      }
    }
  }

  private function addMlsnData(array &$data)
  {
    $dom = new SimpleHTMLDOM();
    $dom->load($this->fetcher->fetch($data['url'], array(
      'track_referrer' => false,
      'strip_comments' => true,
      'strip_html'     => true,
    )));
    $host = parse_url($data['url'], PHP_URL_HOST);


    // Ищем фото
    foreach ($dom->find('a.photos_gallery') as $a) {
      $data['images'][] = sprintf('http://%s%s', $host, $a->href);
    }

    // Выбираем телефоны
    foreach ($dom->find('h2') as $h2) {
      if (false !== strpos($h2->innertext, 'phone2.png')) {
        foreach (preg_split('/[,;]\s*/isU', trim(strip_tags($h2->innertext))) as $part) {
          if ($parsed = Toolkit::unformatPhoneNumber($part, $this->region_id)) {
            $data['phones'][] = Toolkit::formatPhoneNumber(
              $parsed['country'], $parsed['area'], $parsed['number']
            );
          }
        }
        break;
      }
    }
  }

  private function addVolgaInfoData(array &$data)
  {
    $dom = new SimpleHTMLDOM();
    $dom->load($html = $this->fetcher->fetch($data['url'], array(
      'track_referrer' => false,
      'strip_comments' => true,
      'strip_html'     => true,
    )));


    // Ищем фото
    foreach ($dom->find('ul.thumbs img') as $img) {
      $data['images'][] = $img->src;
    }

    // Выбираем телефоны
    foreach ($dom->find('table.sites-table td') as $node) {
      if (false !== mb_strpos($node->innertext, 'Агент')) {
        foreach (preg_split('/[,;]/isU', $node->nextSibling()->innertext) as $part) {
          if ($parsed = Toolkit::unformatPhoneNumber($part, $this->region_id)) {
            $data['phones'][] = Toolkit::formatPhoneNumber(
              $parsed['country'], $parsed['area'], $parsed['number']
            );
          }
        }
        break;
      }
    }
  }


  private function parseItemTitle($title)
  {
    $lot_data = array();

    switch ($this->type) {
      case 'apartament-sale':
      case 'apartament-rent':
        $regex = '/(\d+) комн\.: ([\d.–]+)\/([\d.–]+)\/([\d.–]+)м&#178;, этаж ([\d–]+)\/(\d+)\s*$/iU';

        if (preg_match($regex, $title, $matches)) {
          $lot_data['apartament-sale' == $this->type ? 'field54' : 'field55'] = sprintf(
            '%d%s комнатная квартира',
            $matches[1], ending($matches[1], '', '-х', '-ти', '-ми')
          );

          if ((float) $matches[2]) {
            $lot_data['field1'] = (float) $matches[2];
          }

          if ((float) $matches[3]) {
            $lot_data['field7'] = (float) $matches[3];
          }
          if ((float) $matches[4]) {
            $lot_data['field8'] = (float) $matches[4];
          }

          if ((int) $matches[5]) {
            $lot_data['field3'] = (int) $matches[5];
          }

          $lot_data['field4'] = (int) $matches[6];
        }
        break;

      case 'house-sale':
      case 'house-rent':
        $regex = '/^.*дом.*: ([\d.–]+) кв\.м\.(, участок: ([\d.–]+) сот.+)?$/iU';
        if (preg_match($regex, $title, $matches)) {
          if ((float) $matches[1]) {
            $lot_data['field26'] = (float) $matches[1];
          }
          if (isset($matches[3]) && (float) $matches[3]) {
            $lot_data['field27'] = (float) $matches[3];
          }

          if ('house-sale' == $this->type) {
            if (isset($lot_data['field26'])) {
              $lot_data['field64'] = 'коттедж/дом';
            }
            elseif (isset($lot_data['field27'])) {
              $lot_data['field64'] = 'участок';
            }
          }
        }
        else {
          throw new Exception($title);
        }
        break;

      default:
        throw new Exception('Unable to parse type: ' . $this->type);
    }

    return $lot_data;
  }

  private function parseItemDescription($description)
  {
    $rules = array(
      array('pattern' => '/сдача.+(\d{4})/s', 'field' => 5, 'value' => 1, 'regex' => true),
      array(
        'pattern' => 'свободная планировка',
        'field' => array('apartament-sale' => 54, 'apartament-rent' => 55),
        'value' => 'квартира со свободной планировкой'
      ),

      array(
        'pattern' => 'дом монолитный',
        'field'   => array('apartament-sale' => 6, 'apartament-rent' => 6, 'house-sale' => 28, 'house-rent' => 28),
        'value'   => 'Монолитный'
      ),
      array(
        'pattern' => 'дом кирпичный',
        'field'   => array('apartament-sale' => 6, 'apartament-rent' => 6, 'house-sale' => 28, 'house-rent' => 28),
        'value'   => 'Кирпичный'
      ),
      array(
        'pattern' => 'дом кирпично-монолитный',
        'field'   => array('apartament-sale' => 6, 'apartament-rent' => 6, 'house-sale' => 28, 'house-rent' => 28),
        'value'   => 'Монолитно-кирпичный'
      ),
      array(
        'pattern' => 'дом панельный',
        'field'   => array('apartament-sale' => 6, 'apartament-rent' => 6, 'house-sale' => 28, 'house-rent' => 28),
        'value'   => 'Панельный'
      ),
      array(
        'pattern' => 'дом деревянный',
        'field'   => array('apartament-sale' => 6, 'apartament-rent' => 6, 'house-sale' => 28, 'house-rent' => 28),
        'value'   => 'Дерево'
      ),

      array('pattern' => 'санузел совмещенный', 'field' => 11, 'value' => 'совмещенный'),
      array('pattern' => 'санузел раздельный', 'field' => 11, 'value' => 'раздельный'),

      array('pattern' => '/балкон и.+лоджи/', 'field' => 15, 'value' => 'балкон и лоджия', 'regex' => true),
      array('pattern' => '/балкон/', 'field' => 15, 'value' => 'балкон', 'regex' => true),
      array('pattern' => '/лоджи/', 'field' => 15, 'value' => 'лоджия', 'regex' => true),

      array('pattern' => 'телефон', 'field' => 20, 'value' => 'телефон', 'array' => true),
      array('pattern' => '/мебель/', 'field' => 20, 'value' => 'меблировка', 'array' => true, 'regex' => true),

      array(
        'pattern' => 'электричество',
        'field'   => array('house-sale' => 32, 'house-rent' => 32),
        'value'   => 'да'
      ),
      array(
        'pattern' => 'газ',
        'field'   => array('house-sale' => 31, 'house-rent' => 31),
        'value'   => 'да'
      ),
      array(
        'pattern' => 'канализация',
        'field'   => array('house-sale' => 34, 'house-rent' => 34),
        'value'   => 'да'
      ),
      array(
        'pattern' => 'водопровод',
        'field'   => array('house-sale' => 33, 'house-rent' => 33),
        'value'   => 'да'
      ),
      array(
        'pattern' => 'отопление',
        'field'   => array('house-sale' => 30, 'house-rent' => 30),
        'value'   => 'да'
      ),
    );
    $data = array();

    foreach (preg_split('/\s*,\s*/', $description, 0, PREG_SPLIT_NO_EMPTY) as $value) {
      foreach ($rules as $rule) {
        if (!empty($rule['regex']) && !preg_match($rule['pattern'], $value, $matches)) {
          continue;
        }
        elseif (empty($rule['regex']) && $value != $rule['pattern']) {
          continue;
        }

        if (!empty($rule['regex']) && is_numeric($rule['value'])) {
          $value = $matches[$rule['value']];
        }
        else {
          $value = $rule['value'];
        }

        if (is_array($rule['field'])) {
          if (isset($rule['field'][$this->type])) {
            $key = 'field' . $rule['field'][$this->type];
          }
          else {
            continue;
          }
        }
        else {
          $key = 'field' . $rule['field'];
        }

        if (empty($rule['array'])) {
          $data[$key] = $value;
        }
        else {
          if (!isset($data[$key])) {
            $data[$key] = array();
          }
          $data[$key] = array_unique(array_merge($data[$key], (array) $value));
        }

        break;
      }
    }

    return $data;
  }
}
