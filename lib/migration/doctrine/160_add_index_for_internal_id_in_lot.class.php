<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddIndexForInternalIdInLot160Migration extends Doctrine_Migration_Base
{
    public function up()
    {
      $this->addIndex('lot', 'user_and_internal_id', array(
        'fields' =>
          array(
            0 =>  'user_id',
            1 =>  'internal_id'
          )
      ));
      $this->addIndex('lot', 'internal_id', array(
        'fields' =>
          array(
            0 =>  'internal_id'
          )
      ));
    }

    public function down()
    {}
}