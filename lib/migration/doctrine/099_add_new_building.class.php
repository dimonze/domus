<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddNewBuildingMigration extends Doctrine_Migration
{
	public function up()
	{
    $this->changeColumn('lot', 'type', 'enum', array('values' =>
      array(
              0 => 'apartament-sale',
              1 => 'apartament-rent',
              2 => 'house-sale',
              3 => 'house-rent',
              4 => 'commercial-sale',
              5 => 'commercial-rent',
              6 => 'new-building',
             )));
    $this->changeColumn('form', 'type', 'enum', array('values' =>
      array(
              0 => 'apartament-sale',
              1 => 'apartament-rent',
              2 => 'house-sale',
              3 => 'house-rent',
              4 => 'commercial-sale',
              5 => 'commercial-rent',
              6 => 'new-building',
             )));
	}

	public function down()
	{
    $this->changeColumn('lot', 'type', 'enum', array('values' =>
      array(
              0 => 'apartament-sale',
              1 => 'apartament-rent',
              2 => 'house-sale',
              3 => 'house-rent',
              4 => 'commercial-sale',
              5 => 'commercial-rent',
             )));
    $this->changeColumn('form', 'type', 'enum', array('values' =>
      array(
              0 => 'apartament-sale',
              1 => 'apartament-rent',
              2 => 'house-sale',
              3 => 'house-rent',
              4 => 'commercial-sale',
              5 => 'commercial-rent',
             )));
  }
}