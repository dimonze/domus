<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddUserPhoto097Migration extends Doctrine_Migration
{
	public function up()
	{
		$this->addColumn('user', 'photo', 'string', array(
      'length'  =>  50
    ));
	}

	public function down()
	{
    $this->removeColumn('user', 'photo');
	}
}