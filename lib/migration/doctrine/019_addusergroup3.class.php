<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Addusergroup3Migration extends Doctrine_Migration
{
  public function up()
  {
    $this->addColumn('user', 'group_id', 'integer', array(
      'length'   => 2,
      'unsigned' => true,
    ));
  }

  public function down()
  {
    $this->removeColumn('user', 'group_id');
  }
}