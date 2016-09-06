<?php

/**
 * @see #1507
 */

class UserTypeMigration extends Doctrine_Migration
{
  public function up ()
  {
    $this->addColumn('user', 'type', 'enum', array(
      'notnull'  => true,
      'values'   => array('owner', 'company', 'employee', 'realtor'),
    ));
    
    $this->changeColumn('user', 'credential', 'enum', array(
      'notnull'  => true,
      'values'   => array('user', 'moderator', 'admin'),
      'default'  => 'user',
    ));
    
    $this->removeColumn('user', 'organization_name');
    $this->removeColumn('user', 'organization_site');
    $this->removeColumn('user', 'organization_logo');
  }
  
  public function down ()
  {
    throw new Doctrine_Migration_IrreversibleMigrationException();
  }
}