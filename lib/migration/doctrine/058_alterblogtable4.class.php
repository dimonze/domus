<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AlterBlogTable4Migration extends Doctrine_Migration
{
	public function up()
	{
    $this->addIndex('blog', 'blog_status', array(
      'fields' =>
        array(
          0 => 'status',
        )
    ));
	}

	public function down()
	{
    $this->removeIndex('blog', 'blog_status');
  }
}