<?php

class AlterPostAuthor155Migration extends Doctrine_Migration_Base
{
	public function up()
	{
    $this->addColumn('post_author', 'deleted_at', 'timestamp', array('length' => 25));
	}

	public function down()
	{ }
}