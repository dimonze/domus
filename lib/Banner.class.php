<?php

/**
 * @todo rewrite
 */
class Banner {

  protected static 
    $config_loaded = false,
    $fields = array('key', 'code', 'description');
  protected
    $data = array();


  /**
   * Загружаем конфиг (из кеша)
   * @param bool $force
   * @return bool
   */
  protected static function loadConfig($force = false) {
    return self::$config_loaded =
      self::$config_loaded && !$force ||
      require sfContext::getInstance()->getConfigCache()->checkConfig('config/banners.yml');
  }

  public static function get($key) {
    self::loadConfig();

    $data = array();
    foreach (self::$fields as $field) {
      $data[$field] = sfConfig::get(sprintf('banner_%s_%s', $key, $field));
    }
    $data['key'] = $key;
    
    return new self($data);
  }

  public static function getAll() {
    self::loadConfig();
    
    $rs = array();
    $loaded = array();

    foreach (sfConfig::getAll() as $key => $val) {
      if (strpos($key, 'banner_') === 0) {
        $name = substr($key, 7, strrpos($key, '_') -7);

        if (empty($loaded[$name])) {
          $loaded[$name] = true;
          $rs[] = self::get($name);
        }
      }
    }

    return $rs;
  }

  public static function saveAll() {
    $data = array();
    foreach (self::getAll() as $row) {
      $data[$row->key] = $row->data;
      unset($data[$row->key]['key']);
    }
    $data = array('all' => $data);

    $fs = new sfFilesystem();
    $fs->remove(sfFinder::type('file')->name('config_banners.yml.php')
       ->in(sfConfig::get('sf_cache_dir')));

    return 
      file_put_contents(sfConfig::get('sf_config_dir').'/banners.yml', sfYaml::dump($data, 4)) &&
      self::loadConfig(true);
  }
  
  
  protected function __construct(array $data) {
    $this->data = $data;
  }

  public function __get($field) {
    $getter = 'get' . sfInflector::camelize($field);

    if (method_exists($this, $getter)) {
      return $this->$getter();
    }
    elseif (in_array($field, self::$fields)) {
      return isset($this->data[$field]) ? $this->data[$field] : null;
    }

    throw new Exception(sprintf('Field %s not exists in %s', $field, __CLASS__));
  }

  public function __set($field, $value) {
    $setter = 'set' . sfInflector::camelize($field);

    if (method_exists($this, $setter)) {
      return $this->$setter($value);
    }
    elseif (in_array($field, self::$fields)) {
      return $this->data[$field] = $value &&
             sfConfig::set(sprintf('banner_%s_%s', $this->key, $field), $value);
    }

    throw new Exception(sprintf('Field %s not exists in %s', $field, __CLASS__));
  }

  public function __call($method, $arg) {
    if ($call_type = substr($method, 0, 3)) {
      $field = sfInflector::underscore(substr($method, 3));
      if ($call_type == 'get') {
        return $this->$field;
      }
      elseif($call_type == 'set') {
        return $this->$field = $arg[0];
      }
    }

    throw new Exception(sprintf('Method %s not exists in %s', $method, __CLASS__));
  }

  public function getData() {
    return $this->data;
  }

}
