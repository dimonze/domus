<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddBlogAuthorMigration extends Doctrine_Migration
{
	public function up()
	{
		$this->createTable('blog_author', array(
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
          'length' => 4,
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
    $this->createForeignKey('blog_author', array(
      'local' => 'user_id',
      'foreign' => 'id',
      'foreignTable' => 'user',
      'onUpdate' => NULL,
      'onDelete' => 'CASCADE',
      'name' => 'blog_author_user_id',
    ));
    echo "Table \"blog_author\" created...\r\n";
	}

	public function down()
	{
    $this->dropForeignKey('blog_author', 'blog_author_user_id');
		$this->dropTable('blog_author');
	}
}