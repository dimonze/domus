<?php

class AlterUser156Migration extends Doctrine_Migration_Base
{
	public function up()
	{
    $this->addColumn('user', 'deleted_at', 'timestamp', array('length' => 25));
	}

	public function down()
	{ }
}