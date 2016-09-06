<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddSpecPages148Migration extends Doctrine_Migration
{
	public function up()
	{
    $this->createTable('spec_pages', array(
      'id' => array(
        'type' => 'integer',
        'unsigned' => true,
        'primary' => true,
        'autoincrement' => true,
        'length' => 4,
      ),
      'name' => array(
        'type' => 'string',
        'length' => 100,
      ),
      'text' => array(
        'type' => 'string',
      ),
      'url' => array(
        'type' => 'string',
        'length' => 255,
      )
    ));
	}

	public function down()
	{
    $this->dropTable('spec_pages');
	}
}