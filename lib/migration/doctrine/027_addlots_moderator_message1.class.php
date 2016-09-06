<?php

/**
 * @see #2100
 */

class LotsModeratorMessage1Migration extends Doctrine_Migration
{
  public function up ()
  {
    $this->addColumn('lot', 'moderator_message', 'int', array('length' => 3));
  }
  
  public function down ()
  {
  }
}