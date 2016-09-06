<?php

class AddAgency133Migration extends Doctrine_Migration
{
  public function up()
  {
    $this->createTable(
      'agency', array(
        'id' => array(
          'type' => 'integer',
          'unsigned' => true,
          'primary' => true,
          'autoincrement' => true,
          'length' => 3,
        ),
        'region_id' => array(
          'type' => 'integer',
          'length' => 1,
          'notnull' => true,
          'unsigned' => true,
        ),
        'name' => array(
          'type' => 'string',
          'notnull' => true,
          'length' => 100,
        ),
        'url' => array(
          'type' => 'string',
          'length' => 100,
        ),
        'address' => array(
          'type' => 'string',
          'length' => null,
        ),
        'phones' => array(
          'type' => 'array',
        ),
        'description' => array(
          'type' => 'string',
          'length' => null,
        ),
      ),
      array(
        'indexes' => array(),
        'primary' => array('id'),
      )
    );
    
		$this->createForeignKey('agency', array(
      'local' => 'region_id',
      'foreign' => 'id',
      'foreignTable' => 'region',
      'onUpdate' => NULL,
      'onDelete' => 'cascade',
      'name' => 'agency_region_id',
    ));
  }

  public function down()
  {
    $this->dropForeignKey('agency', 'agency_region_id');
    $this->dropTable('agency');
  }
}