<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddPostAuthor1Migration extends Doctrine_Migration
{
	public function up()
	{
		$this->createTable('post_author', array(
      'id' =>
        array(
        'type' => 'integer',
        'unsigned' => true,
        'primary' => true,
        'autoincrement' => true,
        'length' => 3,
        ),
      'name' =>
        array(
        'type' => 'string',
        'length' => 100,
        ),
      'company' =>
        array(
        'type' => 'string',
        'length' => 100,
        ),
      'post' =>
        array(
        'type' => 'string',
        'length' => 100,
        ),
      'photo' =>
        array(
        'type' => 'string',
        'length' => 20,
        ),
      'description' =>
        array(
        'type' => 'string',
        'length' => 400,
        ),
      'author_type' =>
        array(
        'type' => 'enum',
        'values' =>
        array(
        0 => 'author',
        1 => 'expert',
        ),
        'length' => NULL,
        ),
      'deleted' =>
        array(
        'default' => 0,
        'notnull' => true,
        'type' => 'boolean',
        'length' => 1,
        ),
      ), array(
        'indexes' =>
          array(
            'author_type' =>
              array(
              'fields' =>
                array(
                  0 => 'author_type',
                ),
            ),
          ),
        'primary' =>
          array(
            0 => 'id',
          ),
    ));
	}

	public function down()
	{
		$this->dropTable('post_author');
	}
}