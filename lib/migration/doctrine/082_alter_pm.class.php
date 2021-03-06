<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AlterPmMigration extends Doctrine_Migration
{
	public function up()
	{
    $this->addColumn('p_m', 'user_name', 'string', array(
      'length' => '50',
      'default' => null
     ));
    $this->addColumn('p_m', 'user_email', 'string', array(
      'length' => '60',
      'default' => null
     ));
	}

	public function down()
	{
    $this->removeColumn('p_m', 'user_name');
    $this->removeColumn('p_m', 'user_email');
	}
}