<?php

class AlterLot157Migration extends Doctrine_Migration_Base
{
  public function up()
  {
    $this->addColumn('lot', 'deleted_at', 'timestamp', array('length' => 25));
  }

  public function down()
  { 
    $this->removeColumn('lot', 'deleted_at');
  }
}