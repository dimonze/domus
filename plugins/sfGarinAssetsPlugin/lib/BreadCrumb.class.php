<?php
/**
 * Object interface for breadcrumb instance
 * Using getters and setter for more flexibility
 */
class BreadCrumb
{
  protected
    $text,
    $route,
    $route_params,
    $is_first,
    $is_last;

  public function __construct($text, $route, $route_params)
  {
    $this->text = $text;
    $this->route = $route;
    $this->route_params = $route_params;
  }

  public function __get($name)
  {
    if (property_exists($this, $name)) {
      return $this->$name;
    }
  }

  public function __set($name, $value)
  {
    if (property_exists($this, $name)) {
      $this->$name = $value;
    }
  }

  public function __toString()
  {
    return $this->render();
  }


  public function render()
  {
    if ($this->route) {
      if (!function_exists('link_to')) {
        sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
      }

      return link_to($this->text, $this->route, $this->route_params);
    }
    else {
      return sprintf('<span>%s</span>', $this->text);
    }
  }
}