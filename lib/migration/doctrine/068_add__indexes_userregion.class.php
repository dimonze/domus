<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddIndexesUserRegion068Migration extends Doctrine_Migration
{
	public function up()
	{
    $this->addIndex('user_region', 'user_id', array(
      'fields' =>
        array(
          0 => 'user_id',
        ),      
    ));
    $this->addIndex('user_region', 'region_id', array(
      'fields' =>
        array(
          0 => 'region_id',
        ),      
    ));
	}

	public function down()
	{
		$this->removeIndex('user_region', 'user_id');
		$this->removeIndex('user_region', 'region_id');
	}
}