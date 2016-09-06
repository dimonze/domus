<?php

class AddStreetGeoIndex120Migration extends Doctrine_Migration {

  public function up() {
    $this->addIndex('street', 'geo', array(
             'fields' => 
             array(
              0 => 'latitude',
              1 => 'longitude',
             ),
             ));
    
  }

  public function down() {
    $this->removeIndex('street', 'geo');
  }

}