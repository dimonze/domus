<?php
class DomusCache extends sfCache
{
  protected $_cache_backend;

  public function initialize($options = array())
  {
    $options = array_merge(
      sfConfig::get('app_cache', array()),
      $options
    );
    $this->_cache_backend = new sfMemcacheCache($options);
  }

  /**
   * Gets the cache content for a given key.
   *
   * @param string $key     The cache key
   * @param mixed  $default The default value is the key does not exist or not valid anymore
   *
   * @return mixed The data of the cache
   */
  public function get($key, $default = null)
  {
    return $this->getBackend()->get($key, $default);
  }

  /**
   * Returns true if there is a cache for the given key.
   *
   * @param string $key The cache key
   *
   * @return Boolean true if the cache exists, false otherwise
   */
  public function has($key)
  {
    return $this->getBackend()->has($key);
  }

  /**
   * Saves some data in the cache.
   *
   * @param string $key      The cache key
   * @param mixed  $data     The data to put in cache
   * @param int    $lifetime The lifetime
   *
   * @return Boolean true if no problem
   */
  public function set($key, $data, $lifetime = null)
  {
    return $this->getBackend()->set($key, $data, $lifetime);
  }

  /**
   * Removes a content from the cache.
   *
   * @param string $key The cache key
   *
   * @return Boolean true if no problem
   */
  public function remove($key)
  {
    return $this->getBackend()->remove($key);
  }

  /**
   * Removes content from the cache that matches the given pattern.
   *
   * @param string $pattern The cache key pattern
   *
   * @return Boolean true if no problem
   *
   * @see patternToRegexp
   */
  public function removePattern($pattern)
  {
    return $this->getBackend()->removePattern($pattern);
  }

  /**
   * Cleans the cache.
   *
   * @param string $mode The clean mode
   *                     sfCache::ALL: remove all keys (default)
   *                     sfCache::OLD: remove all expired keys
   *
   * @return Boolean true if no problem
   */
  public function clean($mode = self::ALL)
  {
    return $this->getBackend()->clean($mode);
  }

  /**
   * Returns the timeout for the given key.
   *
   * @param string $key The cache key
   *
   * @return int The timeout time
   */
  public function getTimeout($key)
  {
    return $this->getBackend()->getTimeout($key);
  }

  /**
   * Returns the last modification date of the given key.
   *
   * @param string $key The cache key
   *
   * @return int The last modified time
   */
  public function getLastModified($key)
  {
    return $this->getBackend()->getLastModified($key);
  }


  /**
   * Gets the backend object.
   *
   * @return sfMemcacheCache
   */
  public function getBackend()
  {
    return $this->_cache_backend;
  }
}