<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Addusergroup4Migration extends Doctrine_Migration
{
  public function up()
  {
    $this->createForeignKey('user', array(
      'local' => 'group_id',
      'foreign' => 'id',
      'foreignTable' => 'user_group',
      'onUpdate' => NULL,
      'onDelete' => 'set null',
      'name' => 'user_group_id',
    ));
  }

  public function down()
  {
    $this->dropForeignKey('user', 'user_group_id');
  }
}