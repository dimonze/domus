<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Addblogpostregion extends Doctrine_Migration
{
	public function up()
	{
		$this->createTable('blog_post_region', array(
      'post_id' =>
        array(
          'type' => 'integer',
          'unsigned' => true,
          'primary' => true,
          'length' => 4,
        ),
      'region_id' =>
        array(
          'type' => 'integer',
          'unsigned' => '1',
          'primary' => true,
          'length' => 1,
        ),
      ), array(
        'indexes' =>
          array(
          ),
        'primary' =>
          array(
            0 => 'post_id',
            1 => 'region_id',
          ),
    ));
    $this->createForeignKey('blog_post_region', array(
      'local' => 'post_id',
      'foreign' => 'id',
      'foreignTable' => 'blog_post',
      'onUpdate' => NULL,
      'onDelete' => 'CASCADE',
      'name' => 'blog_post_region_post_id',
    ));
    $this->createForeignKey('blog_post_region', array(
      'local' => 'region_id',
      'foreign' => 'id',
      'foreignTable' => 'region',
      'onUpdate' => NULL,
      'onDelete' => 'CASCADE',
      'name' => 'blog_post_region_region_id',
    ));
	}

	public function down()
	{
    $this->dropForeignKey('blog_post_region', 'blog_post_region_post_id');
    $this->dropForeignKey('blog_post_region', 'blog_post_region_region_id');
		$this->dropTable('blog_post_region');
	}
}