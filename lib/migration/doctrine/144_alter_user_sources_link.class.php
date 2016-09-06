<?php
class AlterUserSourcesLink144Migration extends Doctrine_Migration
{
  public function up()
  {
    $this->addColumn('user_sources_link', 'status', 'enum', array(
      'notnull'  => true,
      'values'   => array('active', 'banned', 'restored'),
      'default' => 'active',
    ));
  }

  public function down()
  {  
    $this->removeColumn('user_sources_link', 'status');
  }
}