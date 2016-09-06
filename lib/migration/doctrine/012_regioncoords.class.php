<?php
/**
* This class has been auto-generated by the Doctrine ORM Framework
*/
class RegionCoordsMigration extends Doctrine_Migration
{
  public function up()
  {
    $regions = Doctrine::getTable('Region')->findAll();

    foreach ($regions as $region) {
      $region->name = str_replace('Аобл', 'обл', $region->name);

      $data = Geocoder::getCoords($region->name);
      if (!$data) {
        throw new Exception(sprintf('No data for "%s"', $region->name));
      }
      $region->fromArray(array(
        'latitude'  => $data['lat'],
        'longitude' => $data['lng'],
        'zoom'      => in_array($region->id, array(77, 78)) ? 10 : 8,
      ));
      $region->save();
    }
  }

  public function down()
  { }
}