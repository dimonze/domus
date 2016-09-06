<?php

class AlterStreet125Migration extends Doctrine_Migration {

  public function up() {   
    $conn  = Doctrine_Manager::getInstance()->getCurrentConnection(); 
    $conn->prepare('ALTER TABLE `street` ADD COLUMN `id` serial FIRST')->execute();     
    
    $this->createForeignKey('street', array(
      'local'        => 'regionnode_id',
      'foreign'      => 'id',
      'foreignTable' => 'regionnode',
      'onDelete'     => 'CASCADE'       
    ));    
  }

  public function down() {   
    $this->dropForeignKey('street', 'street_ibfk_1');
    $this->removeColumn('street', 'id');
  }

}
