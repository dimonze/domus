<?php

class Addflat extends Doctrine_Migration
{
	public function up()
	{
		$this->createTable('flat', array(
             'id' =>
             array(
              'type' => 'integer',
              'unsigned' => true,
              'primary' => true,
              'autoincrement' => true,
              'length' => 8,
             ),
             'lot_id' =>
             array(
              'type' => 'integer',
              'length' => 8,
             ),
             'rooms' =>
             array(
              'type' => 'integer',
              'length' => 8,
             ),
             'common_space' =>
             array(
              'type' => 'integer',
              'length' => 8,
             ),
             'living_space' =>
             array(
              'type' => 'integer',
              'length' => 8,
             ),
             'kitchen_space' =>
             array(
              'type' => 'integer',
              'length' => 8,
             ),
             'floor' =>
             array(
              'type' => 'integer',
              'length' => 8,
             ),
             'has_balcony' =>
             array(
              'type' => 'boolean',
              'length' => 25,
             ),
             'has_loggia' =>
             array(
              'type' => 'boolean',
              'length' => 25,
             ),
             'price' =>
             array(
              'type' => 'integer',
              'length' => 8,
             ),
             'currency' =>
             array(
              'type' => 'enum',
              'values' =>
              array(
              0 => 'rur',
              1 => 'eur',
              2 => 'usd',
              ),
              'length' => NULL,
             ),
             ), array(
             'indexes' =>
             array(
             ),
             'primary' =>
             array(
              0 => 'id',
             ),
             ));
	}

	public function down()
	{
		$this->dropTable('flat');
	}
}