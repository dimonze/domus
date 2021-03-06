<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddBlogPost1Migration extends Doctrine_Migration
{
	public function up()
	{
		$this->createTable('blog_post', array(
      'id' =>
        array(
          'type' => 'integer',
          'unsigned' => true,
          'primary' => true,
          'autoincrement' => true,
          'length' => 4,
        ),
      'blog_id' =>
        array(
          'type' => 'integer',
          'unsigned' => true,
          'length' => 4,
        ),
      'title' =>
        array(
          'type' => 'string',
          'notnull' => true,
          'length' => 255,
        ),
      'created_at' =>
        array(
          'type' => 'timestamp',
          'notnull' => true,
          'length' => 25,
        ),
      'lid' =>
        array(
          'type' => 'string',
          'length' => 500,
        ),
      'body' =>
        array(
          'type' => 'string',
          'notnull' => true,
          'length' => NULL,
        ),
      'status' =>
        array(
        'type' => 'enum',
        'values' =>
          array(
            0 => 'restricted',
            1 => 'publish',
            2 => 'not_publish',
            3 => 'inactive',
            4 => 'moderate',
          ),
        'notnull' => true,
        'default' => 'moderate',
        'length' => NULL,
        ),
      'title_photo' =>
        array(
          'type' => 'string',
          'length' => 50,
        ),
      'title_photo_source' =>
        array(
          'type' => 'string',
          'length' => 200,
        ),
      'title_photo_source_url' =>
        array(
          'type' => 'string',
          'length' => 200,
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
            'created_at_index' =>
              array(
                'fields' =>
                array(
                  0 => 'created_at',
                ),
              ),
            'blog_id_index' =>
              array(
                'fields' =>
                array(
                  0 => 'blog_id',
                ),
              ),
            'status_index' =>
            array(
              'fields' =>
                array(
                  0 => 'status',
                ),
            ),
          ),
        'primary' =>
          array(
            0 => 'id',
          ),
    ));

    $this->createForeignKey('blog_post', array(
      'local' => 'blog_id',
      'foreign' => 'id',
      'foreignTable' => 'blog',
      'onUpdate' => NULL,
      'onDelete' => 'CASCADE',
      'name' => 'blog_post_blog_id',
    ));
	}

	public function down()
	{
    $this->dropForeignKey('blog_post', 'blog_post_blog_id');
		$this->dropTable('blog_post');
	}
}