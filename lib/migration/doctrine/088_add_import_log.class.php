<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddImportLog088Migration extends Doctrine_Migration
{
	public function up()
	{
		$this->createTable('import_log', array(
      'id' =>
        array(
          'type' => 'integer',
          'unsigned' => true,
          'primary' => true,
          'autoincrement' => true,
          'length' => 4,
        ),
      'user_id' =>
        array(
          'type' => 'integer',
          'unsigned' => true,
          'notnull' => true,
          'length' => 4,
        ),
      'file_name' =>
        array(
          'type' => 'string',
          'notnull' => true,
          'length' => 150,
        ),
      'file_type' =>
        array(
          'type' => 'integer',
          'notnull' => true,
          'length' => 1,
        ),
      'created_at' =>
        array(
          'type' => 'timestamp',
          'notnull' => true,
          'length' => 25,
        ),
      ), array(
        'indexes' =>
          array(
          ),
        'primary' =>
          array(
            0 => 'id',
          ),
      )
    );

    $this->addIndex('import_log', 'file_type', array(
      'fields' => array(
        'file_type'
      )
    ));
	}

	public function down()
	{
		$this->dropTable('import_log');
	}
}