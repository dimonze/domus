<?php
class DropSearchResult145Migration extends Doctrine_Migration
{
	public function up()
	{
    $this->dropTable('search_result');
	}

	public function down()
	{
    throw new Doctrine_Migration_IrreversibleMigrationException();
	}
}