<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class CreateIndexesSeotexts096Migration extends Doctrine_Migration
{
	public function up()
	{
		$this->addIndex('seo_texts', 'region_id', array(
      'fields'  =>  array(
        'region_id' => array()
      )
    ));

    $this->addIndex('seo_texts', 'region_node_id', array(
      'fields'  =>  array(
        'region_node_id' => array()
      )
    ));
	}

	public function down()
	{
    $this->removeIndex('seo_texts', 'region_id');
    $this->removeIndex('seo_texts', 'region_node_id');
	}
}