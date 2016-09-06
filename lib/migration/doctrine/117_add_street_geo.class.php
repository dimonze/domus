<?php

class AddStreetGeo117Migration extends Doctrine_Migration {

  public function up() {
    $this->addColumn('street', 'latitude', 'float');
    $this->addColumn('street', 'longitude', 'float');
  }

  public function down() {
    $this->removeColumn('street', 'latitude');
    $this->removeColumn('street', 'longitude');
  }

}