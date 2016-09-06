<?php

/**
 * Toolkit
 *
 * @author     Garin Studio
 */
abstract class Toolkit
{
  private static
    $translit_table = array(
      '"' => '',
      'а' => 'a',
      'б' => 'b',
      'в' => 'v',
      'г' => 'g',
      'д' => 'd',
      'е' => 'e',
      'ё' => 'yo',
      'ж' => 'zh',
      'з' => 'z',
      'и' => 'i',
      'й' => 'y',
      'к' => 'k',
      'л' => 'l',
      'м' => 'm',
      'н' => 'n',
      'о' => 'o',
      'п' => 'p',
      'р' => 'r',
      'с' => 's',
      'т' => 't',
      'у' => 'u',
      'ф' => 'f',
      'х' => 'h',
      'ц' => 'ts',
      'ч' => 'ch',
      'ш' => 'sh',
      'щ' => 'sch',
      'ъ' => 'i',
      'ы' => 'y',
      'ь' => '',
      'э' => 'eh',
      'ю' => 'yu',
      'я' => 'ya',
    );

  public static $months = array(
      1 => 'Январь',
      2 => 'Февраль',
      3 => 'Март',
      4 => 'Апрель',
      5 => 'Май',
      6 => 'Июнь',
      7 => 'Июль',
      8 => 'Август',
      9 => 'Сентябрь',
      10 => 'Октябрь',
      11 => 'Ноябрь',
      12 => 'Декабрь',
   );

  public static $region_hosts;

  public static function getMonthDays($year, $month)
  {
    return date('t', strtotime(sprintf('%d-%d-01', $year, $month)));
  }

  public static function formatPhoneNumber($country, $area, $phone)
  {
    $country = (int) self::preparePhoneBlock($country);
    $country = $country ? $country : 7;
    $area = self::preparePhoneBlock($area);
    $phone = self::preparePhoneBlock($phone);
    if (!$area || !$phone) {
      return null;
    }


    switch (strlen($phone)) {
      case 8:
        $format  = '(\d{3})(\d{3})(\d{2})';
        break;
      case 7:
        $format  = '(\d{3})(\d{2})(\d{2})';
        break;
      case 5:
        $format  = '(\d{1})(\d{2})(\d{2})';
        break;
    }

    if (isset($format)) {
      preg_match("/^$format$/", $phone, $matches);
      $phone = implode('-', array_slice($matches, 1));
    }
    else {
      $phone = chunk_split($phone, 2, '-');
      $phone = substr($phone, 0, strlen($phone) - 1);
    }

    return sprintf('+%s (%s) %s', $country, $area, $phone);
  }

  private static function preparePhoneBlock($num)
  {
    $num = trim($num);
    return preg_replace("/[\D\+]/", '', $num);
  }

  public static function unformatPhoneNumber($phone, $region_id = null)
  {
    $phone = preg_replace('/[^\d\(\)-]+/', '', $phone);

    // some conversions
    if ($region_id && strlen(preg_replace('/\D+/', '', $phone)) <= 7) {
      $phone = sprintf('(%s)%s', self::getRegionPhoneCode($region_id), $phone);
    }
    elseif (preg_match('/^(7|8)(9\d{2})([\d-]{7,})$/', $phone, $matches)) {
      $phone = sprintf('(%s)%s', $matches[2], $matches[3]);
    }
    elseif (strlen(preg_replace('/\D+/', '', $phone)) > 12) {
      return null;
    }

    preg_match('/^(\+?\d{1,2})?[\(-]?(\d+)[\)-](.+)$/', $phone, $matches);

    if (empty($matches[2]) || empty($matches[3])) {
      return null;
    }

    return array(
      'country' => !$matches[1] || 8 == $matches[1] ? '7' : $matches[1],
      'area'    => $matches[2],
      'number'  => preg_replace('/\D+/' ,'', $matches[3])
    );
  }

  public static function generatePassword($length = 8)
  {
    $chars = array_merge(range('0', '9'), range('A', 'Z'), range('a', 'z'));
    $password = '';
    while ($length--) {
      $password .= $chars[array_rand($chars)];
    }
    return $password;
  }

  /**
   * Escaping array or string
   * @param mixed string|array $data
   * @return mixed same variable
   */
  public static function escape ($data)
  {
    if(!sfContext::hasInstance()) {
      $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
      sfContext::createInstance($configuration);
    }
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Escaping'));
    if (is_scalar($data)) {
      return esc_specialchars($data);
    }
    elseif (is_array($data)) {
      foreach ($data as $key => $value) {
        $data[$key] = self::escape($value);
      }
      return $data;
    }
    else {
      return $data;
    }
  }

  /**
   * Log exceptions to file
   * @param string $file
   * @param array $data
   */
  public static function logSection($file, $params)
  {
    file_put_contents($file, var_export($params, true) . PHP_EOL, FILE_APPEND);
  }


  public static function num2str($inn, $money = true, $stripkop = false)
  {
    $nol = 'ноль';
    $str[100]= array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот', 'восемьсот','девятьсот');
    $str[11] = array('','десять','одиннадцать','двенадцать','тринадцать', 'четырнадцать','пятнадцать','шестнадцать','семнадцать', 'восемнадцать','девятнадцать','двадцать');
    $str[10] = array('','десять','двадцать','тридцать','сорок','пятьдесят', 'шестьдесят','семьдесят','восемьдесят','девяносто');
    $sex = array(
      array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),// m
      array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять') // f
    );
    $forms = array(
      array('копейка',  'копейки',   'копеек',     1), // 10^-2
      array('рубль',    'рубля',     'рублей',     0), // 10^ 0
      array('тысяча',   'тысячи',    'тысяч',      1), // 10^ 3
      array('миллион',  'миллиона',  'миллионов',  0), // 10^ 6
      array('миллиард', 'миллиарда', 'миллиардов', 0), // 10^ 9
      array('триллион', 'триллиона', 'триллионов', 0), // 10^12
    );
    $out = $tmp = array();
    // Поехали!
    $tmp = explode('.', str_replace(',','.', $inn));
    $rub = number_format($tmp[0],0,'','-');
    if ($rub==0) $out[] = $nol;
    // нормализация копеек
    $kop = isset($tmp[1]) ? substr(str_pad($tmp[1], 2, '0', STR_PAD_RIGHT),0,2) : '00';
    $segments = explode('-', $rub);
    $offset = sizeof($segments);
    if ((int)$rub==0) { // если 0 рублей
      $o[] = $nol;
      if ($money) {
        $o[] = self::morph(0, $forms[1][0],$forms[1][1],$forms[1][2]);
      }
    }
    else {
      foreach ($segments as $k=>$lev) {
        $sexi= (int) $forms[$offset][3]; // определяем род
        $ri  = (int) $lev; // текущий сегмент
        if ($ri==0 && $offset>1) {// если сегмент==0 & не последний уровень(там Units)
          $offset--;
          continue;
        }
        // нормализация
        $ri = str_pad($ri, 3, '0', STR_PAD_LEFT);
        // получаем циферки для анализа
        $r1 = (int)substr($ri,0,1); //первая цифра
        $r2 = (int)substr($ri,1,1); //вторая
        $r3 = (int)substr($ri,2,1); //третья
        $r22= (int)$r2.$r3; //вторая и третья
        // разгребаем порядки
        if ($ri>99) $o[] = $str[100][$r1]; // Сотни
        if ($r22>20) {// >20
          $o[] = $str[10][$r2];
          $o[] = $sex[ $sexi ][$r3];
        }
        else { // <=20
          if ($r22>9) $o[] = $str[11][$r22-9]; // 10-20
          elseif($r22>0)  $o[] = $sex[ $sexi ][$r3]; // 1-9
        }
        // Рубли
        if ($money) {
          $o[] = self::morph($ri, $forms[$offset][0],$forms[$offset][1],$forms[$offset][2]);
        }
        $offset--;
      }
    }
    // Копейки
    if (!$stripkop) {
      $o[] = $kop;
      $o[] = self::morph($kop,$forms[0][0],$forms[0][1],$forms[0][2]);
    }
    return preg_replace("/\s{2,}/",' ',implode(' ',$o));
  }

  private static function morph($n, $f1, $f2, $f5)
  {
    $n = abs($n) % 100;
    $n1= $n % 10;
    if ($n>10 && $n<20)	return $f5;
    if ($n1>1 && $n1<5)	return $f2;
    if ($n1==1)		return $f1;
    return $f5;
  }

  public static function getRegionPhoneCode($region_id)
  {
    $codes = array(
      77 => '495',
      78 => '812',
    );
    return isset($codes[$region_id]) ? $codes[$region_id] : null;
  }

  public static function buildStoragePath($type, $id, $web = true, $create = true, $source = false)
  {
    if (!$id || !$type) {
      throw new Exception('Can not generate storage path without type or id');
    }

    $base = sprintf('%s/%s', sfConfig::get('sf_upload_dir'), $type);
    $_id = sprintf("%'03s", base_convert((int) $id, 10, 36));
    $path = array_reverse(array_slice(str_split($_id), -3));

    $path[] = $id;
    if ($source) {
      $path[] = 'source';
    }

    $path = $base . '/' . implode('/', $path);

    if ($create && !is_dir($path) && !mkdir($path, 0777, true)) {
      throw new Exception('Can not create storage path');
    }

    if ($web) {
      $path = substr(str_replace(sfConfig::get('sf_web_dir'), '', $path), 1);
    }

    return $path;
  }

  public static function slugify($text, $fix_j = false)
  {
    $table = self::$translit_table;
    if($fix_j) {
      $table['й'] = 'j';
      $table['щ'] = 'shh';
    }
    $text = mb_strtolower($text);
    $text = strtr($text, $table);
    $text = preg_replace('/\W+/', '-', $text);

    return trim($text, '-');
  }

  public static function getGeoHostByRegionId($id = null, $go_to_main = false, $to_subdomain_homepage = false)
  {
    $id = null === $id ? self::getRegionId() : $id;
    $parts = explode('.', sfContext::getInstance()->getRequest()->getHost());
    $host = array_pop($parts) == 'su' ? '.domus.server.garin.su' : '.mesto.ru';

    if(sfConfig::get('is_new_building') && !$go_to_main) {
      if($type = self::getGeoPseudoTypeForNB($id)) {
        return 'http://novostroyki' . $host . '/' . $type;
      }
    }

    if (sfConfig::get('is_new_building') && $to_subdomain_homepage) {
      return 'http://novostroyki' . $host . '/';
    }

    if(sfConfig::get('is_cottage') && !$go_to_main) {
      if($type = self::getGeoPseudoTypeForCottage($id)) {
        return 'http://cottage' . $host . '/' . $type;
      }
    }

    if (sfConfig::get('is_cottage') && $to_subdomain_homepage) {
      return 'http://cottage' . $host . '/';
    }    

    return 'http://'. self::getRegionHostById($id) . $host;
  }

  public static function getGeoHostByLotType($lot)
  {
    $parts = explode('.', sfContext::getInstance()->getRequest()->getHost());
    $host      = array_pop($parts) == 'su' ? '.domus.server.garin.su' : '.mesto.ru';
    
    $type = $lot instanceof Lot ? $lot->type : $lot;
    if ('new_building-sale' == $type) {
      return 'http://novostroyki' . $host;
    }
    elseif ('cottage-sale' == $type) {
      return 'http://cottage' . $host;
    }
    
    if($lot instanceof Lot)
      return self::getGeoHostByRegionId($lot->region_id, true);
    else
      return false;
  }

  public static function getGeoPseudoTypeForNB($id)
  {
    $list  = sfYaml::load(sfConfig::get('sf_config_dir') . '/region_nb.yml');
    if(in_array($id, $list['all'])) {
      return 'novostroyki-' . array_search($id, $list['all']);
    }
    return false;
  }

  public static function getGeoPseudoTypeForCottage($id)
  {
    $list  = sfYaml::load(sfConfig::get('sf_config_dir') . '/region_cottage.yml');
    if(in_array($id, $list['all'])) {
      return 'cottage-' . array_search($id, $list['all']);
    }
    return false;
  }

  public static function getRegionId($debug = false)
  {
    $request = sfContext::getInstance()->getRequest();

    // Check novostroyki
    if($request->getHost() == sfConfig::get('app_new_building_domain')) {
      sfConfig::set('is_new_building', true);
      $uri = $request->getPathInfo() == '/search/get' && $request->isXmlHttpRequest()
        ? $request->getReferer()
        : $request->getPathInfo();
      preg_match('#/novostroyki-([\w\-]+)#', $uri, $matches);
      $region_id = null;
      if(!empty($matches[1])) {
        $list = sfYaml::load(sfConfig::get('sf_config_dir') . '/region_nb.yml');
        if(array_key_exists($matches[1], $list['all'])) {
          $region_id = $list['all'][$matches[1]];
        }
      }
      elseif (preg_match('#/(nedvijimost|novostroyki|cottage)/#', $uri) || preg_match('#/(news|posts|experts|authors|qa)#', $uri)) {
        $region_id = $_COOKIE['current_region'];
      }
      return $region_id ? $region_id : 77;
    }

    // Check cottage
    if($request->getHost() == sfConfig::get('app_cottage_domain')) {
      sfConfig::set('is_cottage', true);
      $uri = $request->getPathInfo() == '/search/get' && $request->isXmlHttpRequest()
        ? $request->getReferer()
        : $request->getPathInfo();
      preg_match('#/cottage-([\w\-]+)#', $uri, $matches);
      $region_id = null;
      if(!empty($matches[1])) {
        $list = sfYaml::load(sfConfig::get('sf_config_dir') . '/region_cottage.yml');
        if(array_key_exists($matches[1], $list['all'])) {
          $region_id = $list['all'][$matches[1]];
        }
      }
      elseif (preg_match('#/(nedvijimost|novostroyki|cottage)/#', $uri) || preg_match('#/(news|posts|experts|authors|qa)#', $uri)) {
        $region_id = $_COOKIE['current_region'];
      }
      return $region_id ? $region_id : 50;
    }

    $parts = explode('.', $request->getHost());
    return self::getRegionIdByHost($parts[0]);
  }

  public static function getLotUrlForWorkers($host, $lot)
  {
    $host = ('new_building-sale' == $lot->type) ? 'novostroyki.' . $host : ('cottage-sale' == $lot->type) ? 'cottage.' . $host : self::getRegionHostById($lot->region_id) . '.' . $host;

    if (null === $lot->slug) {
      $type = $lot->type;
      $slug = $lot->id;
    }
    else {
      $type = ('new_building-sale' == $lot->type) ? 'novostroyki' : 'nedvijimost';
      $type = ('cottage-sale' == $lot->type) ? 'cottage' : $type;
      $slug = $lot->slug . '.html';
    }

    return sprintf('http://%s/%s/%s#top', $host, $type, $slug);
  }

  public static function getRegionHostById($id)
  {
    self::loadRegionHosts();
    return array_search($id, self::$region_hosts);
  }

  public static function getRegionIdByHost($host)
  {
    self::loadRegionHosts();
    return array_key_exists($host, self::$region_hosts) ? self::$region_hosts[$host] : false;
  }

  protected static function loadRegionHosts()
  {
    if (!self::$region_hosts) {
      $data = sfYaml::load(sfConfig::get('sf_config_dir') . '/region.yml');
      self::$region_hosts = $data['all'];
    }
  }

  public static function isSubdomain($type)
  {
    return in_array($type, Lot::$_subdomains);
  }

  public static function curl_get_file_contents($url)
  {
    $c = curl_init();
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($c, CURLOPT_URL, $url);
    $contents = curl_exec($c);
    curl_close($c);

    if ($contents) {
      return $contents;
    }
    else {
      return false;
    }
  }
}
