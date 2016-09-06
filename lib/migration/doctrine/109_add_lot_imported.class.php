<?php

class AddLotImported109Migration extends Doctrine_Migration {

  public function up() {
    $this->addColumn('lot', 'imported', 'boolean', array(
        'default' => 0
    ));
  }

  public function down() {
    $this->removeColumn('lot', 'imported');
  }

}