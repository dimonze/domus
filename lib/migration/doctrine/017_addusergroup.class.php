<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddusergroupMigration extends Doctrine_Migration
{
	public function up()
	{
		$this->createTable('user_group', array(
             'id' => 
             array(
              'type' => 'integer',
              'unsigned' => true,
              'autoincrement' => true,
              'primary' => true,
              'length' => 2,
             ),
             'name' => 
             array(
              'type' => 'string',
              'notnull' => true,
              'unique' => true,
              'length' => 100,
             ),
             'credentials' => 
             array(
              'type' => 'array',
              'notnull' => true,
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
		$this->dropTable('user_group');
	}
}