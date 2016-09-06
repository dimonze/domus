<?php
/**
 * Класс для общения с сервисом 'склонятор' от Яндекса
 * Умеет кешировать, кеширует надолго
 * @see http://nano.yandex.ru/project/inflect/
 * @see http://morpher.ru/WebServices/Morpher.aspx
 */
class WordInflector
{
  const
    PROVIDER_MORPHER   =  1,
    PROVIDER_YANDEX    =  2,
    PROVIDER_AUTO      = -1,

    TYPE_NOMATIVE      = 1,
    TYPE_GENITIVE      = 2,
    TYPE_DATIVE        = 3,
    TYPE_ACCUSATIVE    = 4,
    TYPE_INSTRUMENTAL  = 5,
    TYPE_PREPOSITIONAL = 6;

  protected static
    $_cache,
    $_provider = self::PROVIDER_AUTO,
    $_providers = array(
      self::PROVIDER_MORPHER  =>  'fetchMorpher',
      self::PROVIDER_YANDEX   =>  'fetchYandex'
    ),
    $_curl_opts = array(
      CURLOPT_HEADER          => false,
      CURLOPT_TIMEOUT         => 3,
      CURLOPT_RETURNTRANSFER  => true,
      CURLOPT_FOLLOWLOCATION  => true
    ),
    $_curl = false,
    $_morpher_ban = false;

  /**
   * Get word in 'type' case
   * @param string $string
   * @param integer $type
   * @return string
   */
  public static function get($string, $type = self::TYPE_NOMATIVE, $provider = null)
  {
    //http://dev.garin.su/issues/18424#note-15
    if($string == 'Крым' && $type == self::TYPE_PREPOSITIONAL) return 'Крыму';

    $string = trim($string);
    mb_internal_encoding('UTF-8');
    
    // some pre-processing
    if (preg_match('/^[А-Я]+$/u', $string)) {
      return $string;
    }
    elseif (strpos($string, '/')) {
      $parts = explode('/', $string);
      foreach ($parts as $i => $part) {
        $parts[$i] = self::get($part, $type);
      }
      return implode('/', $parts);
    }
    elseif (strpos($string, '. ')) {
      list($base, $string) = explode('. ', $string, 2);
      return $base . '. ' . self::get($string, $type);
    }
    
    if ($result = self::fetch($string, $type, $provider)) {
      return $result;
    }
    else {
      if ($provider == self::PROVIDER_MORPHER) {
        return null;
      }
      else {
        return $string;
      }
    }
  }

  /**
   * Set data provider
   * @see PROVIDER_* constants
   * @param integer $provider
   * @return void
   */
  public static function setProvider($provider)
  {
    self::$_provider = $provider;
  }

  /**
   * Fetch
   * @param string $word
   * @param integer $type
   * @return string
   */
  protected static function fetch($word, $type, $provider = null)
  {
    //http://dev.garin.su/issues/18424#note-15
    if($word == 'Крым' && $type == self::TYPE_PREPOSITIONAL) return 'Крыму';
    
    $cache_key = md5($word);

    if (self::getCache()->has($cache_key)) {
      $data = unserialize(self::getCache()->get($cache_key));
    }
    else {      
      if (null !== $provider) {
        $data = self::fetchProvider($provider, $word, $type);
      }
      else {        
        if (self::$_provider == self::PROVIDER_AUTO) {
          foreach (self::$_providers as $provider_id => $func) {
            if ($provider_id === self::PROVIDER_MORPHER
                && strtotime(self::$_morpher_ban . '+24 hours') > time()) {              
              continue;
            }
            else {
              $data = self::fetchProvider($provider_id, $word, $type);
              if (null != $data) {
                break;
              }
            }
          }
        }
        else {
          $data = self::fetchProvider(null, $word, $type);
        }
      }
      if (null === $data) {
        return $word;
      }
      if (!empty($data[$type])) {
        self::getCache()->set($cache_key, serialize($data));
      }
    }
    return !(empty($data[$type])) ? $data[$type] : null;
  }

  protected static function fetchProvider($provider = null, $word, $type)
  {
    $provider = (null === $provider) ? self::$_provider : $provider;
    $func = self::$_providers[$provider];    
    $data = call_user_func('WordInflector::' . $func, $word);    
    if (null === $data) {
      self::fetch($word, $type);
    }
    return $data;
  }

  /**
   * Fetch word-info using morpher.ru service
   * @param string $word
   * @return array
   * @throws Exception
   */
  protected static function fetchMorpher($word)
  {
    $url = sprintf('http://morpher.ru/WebServices/Morpher.asmx/GetForms?s=%s', urlencode($word));
    $response = self::curlGetContents($url);    
    if ($response) {
      $document = new DOMDocument('1.0', 'utf-8');
      if (!$document->load($url)) {
        throw new Exception('Unable to load data!');
      }
      $elements = $document->getElementsByTagName('string');

      $data = array(1 => $word);
      if ($elements->length > 1) {
        foreach ($elements as $element) {
          if (preg_match('/лимит/', $element->nodeValue)) {
            self::$_morpher_ban = date('Y-m-d H:i:s');            
            return null;
          }
          $data[] = $element->nodeValue;
        }
      }
      return $data;
    }
    return null;
  }

  /**
   * Fetch word-info using nano.yandex.ru service
   * @param string $word
   * @return array
   * @throws Exception
   */
  protected static function fetchYandex($word)
  {
    $url = sprintf('http://export.yandex.ru/inflect.xml?name=%s&format=json', urlencode($word));
    $response = self::curlGetContents($url);
    if ($response) {
      $data = json_decode($response, true);
      return $data;
    }
    return null;
  }

  /**
   * Get the Cache
   * @return sfCache
   */
  protected static function getCache()
  {
    if (!self::$_cache) {
      self::$_cache = new sfFileCache(array(
        'cache_dir' => sfConfig::get('sf_cache_dir') . '/w-inflector',
        'lifetime'     => 15 * 24 * 3600,
      ));
    }

    return self::$_cache;
  }

  protected static function curlGetContents($url)
  {
    $ch = curl_init();
    curl_setopt_array($ch, self::$_curl_opts);
    curl_setopt($ch, CURLOPT_URL, $url);
    $data = curl_exec($ch);

    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
      curl_close($ch);
      return false;
    }
    curl_close($ch);
    return $data;
  }
}