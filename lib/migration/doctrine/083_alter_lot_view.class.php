<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AlterLotView083Migration extends Doctrine_Migration
{
	public function up()
	{    
    $this->addColumn('lot_view', 'lot_type', 'enum', array(
      'values' =>
        array(
        0 => 'apartament-sale',
        1 => 'apartament-rent',
        2 => 'house-sale',
        3 => 'house-rent',
        4 => 'commercial-sale',
        5 => 'commercial-rent',
        ),
      'length'  => NULL,
      'default' =>  'apartament-sale'

     ));
	}

	public function down()
	{
    $this->removeColumn('lot_view', 'lot_type');
	}
}