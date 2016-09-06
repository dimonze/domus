<?php
/**
 * Basic class for breadcrumbs
 * Write the renderer youself
 */
class BreadCrumbs
{
  protected static $crumbs = array();

  /**
   * Add a breadcrumb item to the end
   * @param string $text
   * @param mixed $route string or array with route name and options
   */
  public static function add()
  {
    $args = func_get_args();

    // Checking for text
    if (empty($args[0])) {
      throw new Exception('Empty breadcrumbs is not allowed');
    }

    $text = $args[0];
    $route = null;
    $route_params = null;

    // Looking for route/url
    if (!empty($args[1])) {
      if (is_array($args[1])) {
        $route = $args[1][0];
        $route_params = $args[1][1];
      }
      else {
        $route = $args[1];
      }
    }

    self::$crumbs[] = new BreadCrumb($text, $route, $route_params);
  }

  /**
   * Set the breadcumbs
   * @param array $items passed to add()
   */
  public static function set(array $items)
  {
    self::clear();
    foreach ($items as $item) {
      call_user_func_array('BreadCrumbs::add', (array) $item);
    }
  }

  /**
   * Empty all breadcrumbs
   */
  public static function clear()
  {
    self::$crumbs = array();
  }

  /**
   * Get the breadcrumb(s)
   * If index specified one item will be returned
   * overwise all the breadcrumbs.
   * Item structure:
   *  * text
   *  * route
   *  * route_params
   *  * is_first
   *  * is_last
   * @param mixed $index integer or first/last
   * @return mixed array|null
   */
  public static function get($index = null)
  {
    self::fixPositions();
    
    if ($index) {
      if ($index == 'first') {
        $index = 0;
      }
      elseif ($index == 'last') {
        $index = self::nb() - 1;
      }

      if (isset(self::$crumbs[$index])) {
        return self::$crumbs[$index];
      }
      else {
        return null;
      }
    }
    else {
      return self::$crumbs;
    }
  }

  public static function nb()
  {
    return count(self::$crumbs);
  }

  private static function fixPositions()
  {
    $last = self::nb() - 1;
    for ($i = 0; $i <= $last; $i++) {
      self::$crumbs[$i]->is_first = (0 == $i);
      self::$crumbs[$i]->is_last  = ($last == $i);
    }
  }
}