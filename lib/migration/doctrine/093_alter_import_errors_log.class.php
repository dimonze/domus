<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AlterImportErrorsLog093Migration extends Doctrine_Migration
{
	public function up()
	{
    $this->addColumn('import_errors_log', 'message', 'string', array('length' => 255));
	}

	public function down()
	{
    $this->removeColumn('import_errors_log', 'message');
	}
}