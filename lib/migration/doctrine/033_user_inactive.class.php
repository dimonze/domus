<?php
/**
* @see #2107
*/
class UserAddInactive1Migration extends Doctrine_Migration
{
  public function up()
  {
    $this->addColumn('user', 'inactive', 'boolean', array(
        'notnull' => true,
        'default' => 0
    ));
  }

  public function down()
  { }
}