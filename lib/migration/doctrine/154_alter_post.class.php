<?php

class AlterPost154Migration extends Doctrine_Migration_Base
{
	public function up()
	{
    $this->addColumn('post', 'deleted_at', 'timestamp', array('length' => 25));
	}

	public function down()
	{ }
}