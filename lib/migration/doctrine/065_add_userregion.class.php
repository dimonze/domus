<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddUserRegion065Migration extends Doctrine_Migration
{
	public function up()
	{
		$this->createTable('user_region', array(
        'user_id' =>
          array(
          'type' => 'integer',
          'unsigned' => true,
          'notnull' => true,
          'length' => 4,
          ),
        'region_id' =>
          array(
            'type' => 'integer',
            'unsigned' => true,
            'notnull' => true,
            'length' => 1,
          ),
        'lots_count' =>
          array(
            'type' => 'integer',
            'unsigned' => true,
            'notnull' => true,
            'default' => 0,
            'length' => 2,
          ),
      ),
      array(
      'indexes' =>
        array(
        ),
      'primary' =>
        array(
          0 => 'user_id',
          1 => 'region_id',
        ),
      ));
	}

	public function down()
	{
		$this->dropTable('user_region');
	}
}