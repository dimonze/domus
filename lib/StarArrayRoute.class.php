<?php

/**
 * extending sfRoute for parsing arrays in 'star' params
 *
 * @package    symfony
 * @subpackage routing
 * @author     Eugeniy Belyaev
 * @version    SVN: $Id$
 */
class StarArrayRoute extends sfRoute
{
  protected function parseStarParameter($star)
  {
    $tmp = explode('/', $star);
    $path = '';
    for ($i = 0, $max = count($tmp); $i < $max; $i += 2) {
      if (!empty($tmp[$i])) {
        $path .= $tmp[$i] . '=' . (isset($tmp[$i + 1]) ? urldecode($tmp[$i + 1]) : 1) . '&';
      }
    }
    parse_str($path, $parameters);

    return $parameters;
  }
}
