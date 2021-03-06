<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Region extends BaseRegion
{
  public function getDefaultSearchCoords () {
    $width = 757;
    $height = 348;
    $zoom = $this->zoom;

    list($x, $y) = Geocoder::getPxByLatLng($this->latitude, $this->longitude, $zoom, false);

    $lat = $lng = array();
    list($lat['from'], $lng['from']) = Geocoder::getLatLngByPx($x - $width / 2, $y + $height /2, $zoom, false);
    list($lat['to'], $lng['to']) = Geocoder::getLatLngByPx($x + $width / 2, $y - $height /2, $zoom, false);

    return array($lat, $lng);
  }

  public function  hasLots($type)
  {
    $options = array(
      'limit' => 1
    );
    $params = array(
      'type' => $type,
      'region_id' =>  $this->id
    );
    $sphinx = new DomusSphinxClient($options);
    $sphinx->getRegionLotsCount($params);
    $result = $sphinx->getRes();
    $sphinx->Close();
    
    return (count($result)) ? true : false;
  }
  
  public function getFullNamePrepositional() {
    $full_name = trim(str_replace('обл.', '', $this->name));
    $full_name = trim(str_replace('г.', '', $full_name));
    
    $name = WordInflector::get(
      $full_name,
      WordInflector::TYPE_PREPOSITIONAL
    ) . ((in_array($this->id, array(77,78,1))) ? '' : ' области');
    
    if (null == $name) {
      return $this->name;
    }
    
    return $name;
  }
}
