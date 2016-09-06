<?php
class AddUserIndexes136Migration extends Doctrine_Migration
{
	public function up()
	{
    $this->addIndex('user', 'email_index', array(
      'fields' => array('email'),
    ));
	}

	public function down()
	{
    $this->removeIndex('user', 'email_index');
	}
}