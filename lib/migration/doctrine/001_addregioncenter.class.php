<?php

/**
 * @see #1337
 */

class AddRegionCenterMigration extends Doctrine_Migration
{
  public function up ()
  {
    $this->addColumn('region', 'latitude', 'float');
    $this->addColumn('region', 'longitude', 'float');
    $this->addColumn('region', 'zoom', 'integer', array(
      'length' => 1,
      'unsigned' => true,
    ));
  }
  public function down ()
  {
    $this->removeColumn('region', 'latitude');
    $this->removeColumn('region', 'longitude');
    $this->removeColumn('region', 'zoom');
  }
}