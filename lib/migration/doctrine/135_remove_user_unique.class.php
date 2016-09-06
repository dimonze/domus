<?php
class RemoveUserUnique135Migration extends Doctrine_Migration
{
	public function up()
	{
    $this->removeIndex('user', 'email_index');
    $this->removeIndex('user', 'phone_index');
	}

	public function down()
	{
    throw new Doctrine_Migration_IrreversibleMigrationException();
	}
}