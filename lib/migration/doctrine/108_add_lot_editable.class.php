<?php

class AddLotEditable108Migration extends Doctrine_Migration {

  public function up() {
    $this->addColumn('lot', 'editable', 'boolean', array(
        'default' => true
    ));
  }

  public function down() {
    $this->removeColumn('lot', 'editable');
  }

}