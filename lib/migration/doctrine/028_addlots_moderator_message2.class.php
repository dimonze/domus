<?php

/**
 * @see #2100
 */

class LotsModeratorMessage2Migration extends Doctrine_Migration
{
  public function up ()
  {  
    $this->createForeignKey('lot', array(
      'local' => 'moderator_message',
      'foreign' => 'id',
      'foreignTable' => 'p_m',
      'onDelete' => 'cascade',
      'name' => 'moderator_message_id',
    ));
  }
  
  public function down ()
  {
    $this->dropForeignKey('lot', 'moderator_message_id');
    $this->removeColumn('lot', 'moderator_message');
  }
}