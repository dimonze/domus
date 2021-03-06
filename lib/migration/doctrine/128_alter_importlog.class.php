<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AlterImportLog128Migration extends Doctrine_Migration
{
	public function up()
	{
    $this->addColumn('import_log', 'lots', 'integer', array(
      'unsigned'  => true,
      'notnull'   => true,
      'default'   => 0,
      'length'    => 2,  
    ));
	}

	public function down()
	{
		$this->removeColumn('import_log', 'lots');
	}
}