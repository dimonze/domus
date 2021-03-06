<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AlterUserSourcesLink147Migration extends Doctrine_Migration
{
	public function up()
	{
		$this->addColumn('user_sources_link', 'frequency', 'integer', array(
      'notnull' => true,
      'default' => UserSourcesLink::FREQUENCY_4,
      'length' => 1,
    ));
	}

	public function down()
	{
		$this->removeColumn('user_sources_link', 'frequency');
	}
}