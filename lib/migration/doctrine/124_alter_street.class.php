<?php

class AlterStreet124Migration extends Doctrine_Migration {

  public function up() {   
    $this->dropForeignKey('street', 'street_ibfk_1');
    $this->dropConstraint('street', 'PRIMARY', true);
  }

  public function down() {   
     throw new Doctrine_Migration_IrreversibleMigrationException();
     
  }

}
