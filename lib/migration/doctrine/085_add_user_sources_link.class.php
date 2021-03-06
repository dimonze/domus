<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddUserSourcesLink085Migration extends Doctrine_Migration
{
	public function up()
	{
		$this->createTable('user_sources_link', array(
      'id' =>
        array(
          'type'          => 'integer',
          'unsigned'      => true,
          'primary'       => true,
          'autoincrement' => true,
          'length'        => 5,
        ),
      'user_id' =>
        array(
          'type'      => 'integer',
          'unsigned'  => true,
          'length'    => 4,
          'notnull'   =>  true
        ),
      'url' =>
        array(
          'type'      => 'string',
          'notnull'   => true,
          'length'    => 255,
        ),
      'type' =>
        array(
          'type'      => 'integer',
          'notnull'   => true,
          'length'    => 1,
        ),
      'file_type' =>
        array(
          'type'      => 'integer',
          'notnull'   => true,
          'length'    => 1,
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

    $this->addIndex('user_sources_link', 'type', array(
      'fields' => array(
        'type'
      )
    ));
    $this->addIndex('user_sources_link', 'file_type', array(
      'fields' => array(
        'file_type'
      )
    ));
	}

	public function down()
	{
		$this->dropTable('user_sources_link');
	}
}