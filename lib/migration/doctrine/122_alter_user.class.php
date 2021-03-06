<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AlterUser122Migration extends Doctrine_Migration
{
  public function up()
  {
    $this->addColumn('user', 'approved', 'boolean', 
      array(
        'notnull' => false,
        'default' => 0       
      )
    );
    
  }

  public function down()
  {
    $this->removeColumn('user', 'approved');
  }
}
