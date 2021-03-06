<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddClaimTheme071Migration extends Doctrine_Migration
{
	public function up()
	{
		$this->createTable('claim_theme', array(
      'id' =>
        array(
          'type' => 'integer',
          'unsigned' => true,
          'primary' => true,
          'autoincrement' => true,
          'length' => 4,
        ),
      'title' =>
        array(
          'type' => 'string',
          'notnull' => true,
          'length' => 255,
        )
    ));
	}

	public function down()
	{
    $this->dropTable('claim_theme');
	}
}