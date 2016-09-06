<?php

class AlterLot153Migration extends Doctrine_Migration
{
	public function up()
	{
    $this->addColumn('lot', 'auto_description', 'string', array('length' => 1500));
	}

	public function down()
	{
		$this->removeColumn('lot', 'auto_description');
	}
}