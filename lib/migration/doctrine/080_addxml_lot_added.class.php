<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddXmlLotAddedMigration extends Doctrine_Migration {

  public function up()
  {
    $this->createTable('xml_lot_added', array(
      'id' =>
      array(
        'type' => 'integer',
        'unsigned' => true,
        'primary' => true,
        'autoincrement' => true,
        'length' => 4,
      ),
      'internal_id' =>
      array(
        'type' => 'integer',
        'unsigned' => true,
        'notnull' => true,
        'length' => 40,
      ),
      'lot_id' =>
      array(
        'type' => 'integer',
        'unsigned' => true,
        'notnull' => true,
        'length' => 4,
      ),
      'user_id' =>
      array(
        'type' => 'integer',
        'unsigned' => true,
        'length' => 4,
      ),
      ), array(
      'indexes' =>
      array(
        'internal_id' =>
        array(
          'fields' =>
          array(
            0 => 'internal_id',
          ),
        ),

      ),
              'primary' =>
        array(
          0 => 'id',
        ),
    ));

    $this->createForeignKey('xml_lot_added', array(
      'local' => 'user_id',
      'foreign' => 'id',
      'foreignTable' => 'user',
      'onUpdate' => NULL,
      'onDelete' => 'CASCADE',
      'name' => 'xml_lot_added_user_id',
    ));

    $this->createForeignKey('xml_lot_added', array(
      'local' => 'lot_id',
      'foreign' => 'id',
      'foreignTable' => 'lot',
      'onUpdate' => NULL,
      'onDelete' => 'CASCADE',
      'name' => 'xml_lot_added_lot_id',
    ));
  }

  public function down()
  {
    $this->dropForeignKey('xml_lot_added', 'xml_lot_added_lot_id');
    $this->dropForeignKey('xml_lot_added', 'xml_lot_added_user_id');
    $this->dropTable('xml_lot_added');
  }

}