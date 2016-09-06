<?php

abstract class Geocoder
{
  /**
   * @see http://code.google.com/apis/maps/documentation/reference.html#GGeoStatusCode
   */
  const
    G_GEO_SUCCESS              = 'OK',
    G_GEO_BAD_REQUEST          = 'INVALID_REQUEST',
    G_GEO_SERVER_ERROR         = 'ZERO_RESULTS',
    G_GEO_MISSING_QUERY        = 'ZERO_RESULTS',
    G_GEO_MISSING_ADDRESS      = 'ZERO_RESULTS',
    G_GEO_UNKNOWN_ADDRESS      = 'ZERO_RESULTS',
    G_GEO_UNAVAILABLE_ADDRESS  = 'INVALID_REQUEST',
    G_GEO_UNKNOWN_DIRECTIONS   = 'INVALID_REQUEST',
    G_GEO_TOO_MANY_QUERIES     = 'OVER_QUERY_LIMIT';

  private static
    $fetcher;

  /**
   * Get info about $location
   * @param string $location
   * @return stdClass|null
   * @throws Exception
   */
  public static function getPlacemark($location)
  {
    $data = self::request($location);
    return isset($data->results[0]) ? $data->results[0] : null;
  }

  /**
   * Get $location coords
   * @param string $location
   * @return array|null array('lat' => float, 'lng' => float)
   * @throws Exception
   */
  public static function getCoords($location)
  {
    if ($placemark = self::getPlacemark($location)) {
      if ($coords = $placemark->geometry->location) {
        return array(
          'lat' => $coords->lat,
          'lng' => $coords->lng,
        );
      }
    }

    return null;
  }

  /**
   * Convert lat/lng position to absolute pixel position with $zoom
   * @see original http://www.usnaviguide.com/google-tiles.htm
   * @param float $lat
   * @param float $lng
   * @param integer $zoom
   * @param boolean $return_assoc
   * @return array ('x' => integer, 'y' => integer)
   */
  public static function getPxByLatLng($lat, $lng, $zoom, $return_assoc = true)
  {
    $data = array(
      (int) (pow(2, $zoom + 8) / 2 + $lng * (pow(2, $zoom + 8) / 360)),
      (int) (pow(2, $zoom + 8) / 2
               + log((1 + sin($lat * M_PI / 180)) / (1 - sin($lat * (M_PI / 180))))
               * -0.5 * (pow(2, $zoom + 8) / (2 * M_PI))),
    );
    return $return_assoc ? array_combine(array('x', 'y'), $data) : $data;
  }

  /**
   * Convert absolute pixel position zoomed to $zoom to lat/lng
   * @see original http://www.usnaviguide.com/google-tiles.htm
   * @param integer $x
   * @param integer $y
   * @param integer $zoom
   * @param boolean $return_assoc
   * @return array ('lat' => float, 'lng' => float)
   */
  public static function getLatLngByPx($x, $y, $zoom, $return_assoc = true)
  {
    $data = array(
      (2 * atan(exp(-(($y - pow(2, $zoom + 8) / 2) / (pow(2, $zoom + 8) / (2 * M_PI))))) - M_PI / 2)
         / (M_PI / 180),
      ($x - pow(2, $zoom + 8) / 2) / (pow(2, $zoom + 8) / 360),
    );

    return $return_assoc ? array_combine(array('lat', 'lng'), $data) : $data;
  }


  /**
   * Do request
   * @param string $query
   * @param boolean $retry_on_quota
   * @return stdClass
   * @throws Exception
   */
  private static function request($query, $retry_on_quota = true)
  {
    if (function_exists('curl_init')) {
      if (!self::$fetcher) {
        self::$fetcher = new Fetcher(array('track_referrer' => false));
      }
      $ch = self::$fetcher->getCurl(sprintf(
        'http://maps.google.com/maps/api/geocode/json?address=%s&sensor=false&language=ru',
        urlencode($query))
      );
      $data = curl_exec($ch);
    }
    else {
      $data = file_get_contents(sprintf(
        'http://maps.google.com/maps/api/geocode/json?address=%s&sensor=false&language=ru',
        urlencode($query)), 0, ParseTools::getStreamContext());
    }

    if (!$data || !($data = json_decode($data))) {
      throw new Exception('Geocoder request failed - empty response');
    }

    if ($data->status != self::G_GEO_SUCCESS) {
      switch ($data->status) {
        case self::G_GEO_BAD_REQUEST:
        case self::G_GEO_MISSING_QUERY:
          throw new Exception(sprintf('Geocoder request failed with status code - "%s"', $data->status));
          break;

        case self::G_GEO_MISSING_ADDRESS:
        case self::G_GEO_UNKNOWN_ADDRESS:
        case self::G_GEO_UNAVAILABLE_ADDRESS:
        case self::G_GEO_UNKNOWN_DIRECTIONS:
          return $data;
          break;

        case self::G_GEO_TOO_MANY_QUERIES:
          if ($retry_on_quota) {
            sleep(2);
            return self::request($query, false);
          }
          else {
            throw new Exception('Geocoder limit reached');
          }
          break;

        default:
          throw new Exception(sprintf('Geocoder returned unknown status code - "%s"', $data->Status->code));
          break;
      }
    }

    return $data;
  }
}