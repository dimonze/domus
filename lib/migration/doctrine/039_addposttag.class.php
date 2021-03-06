<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddPostTag1Migration extends Doctrine_Migration
{
	public function up()
	{
		$this->createTable('post_tag', array(
        'post_id' =>
          array(
          'type' => 'integer',
          'unsigned' => true,
          'notnull' => true,
          'primary' => true,
          'length' => 3,
          ),
        'tag_id' =>
          array(
          'type' => 'integer',
          'unsigned' => true,
          'notnull' => true,
          'primary' => true,
          'length' => 3,
          ),
      ), array(
        'indexes' =>
          array(
          ),
        'primary' =>
          array(
            0 => 'post_id',
            1 => 'tag_id',
          ),
    ));

    $this->createForeignKey('post_tag', array(
      'local' => 'post_id',
      'foreign' => 'id',
      'foreignTable' => 'post',      
      'onDelete' => 'CASCADE',
      'name' => 'post_tag_post_id',
    ));

    $this->createForeignKey('post_tag', array(
      'local' => 'tag_id',
      'foreign' => 'id',
      'foreignTable' => 'tag',
      'name' => 'post_tag_tag_id',
    ));
	}

	public function down()
	{
    $this->dropForeignKey('post_tag', 'post_tag_post_id');
    $this->dropForeignKey('post_tag', 'post_tag_tag_id');
		$this->dropTable('post_tag');
	}
}