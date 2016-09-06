<?php
class ChangeAgency139Migration extends Doctrine_Migration
{
	public function up()
	{
    $this->addColumn('agency', 'type', 'enum', array(
      'values'  => array('company', 'bti'),
      'default' => 'company',
      'notnull' => true,
    ));
	}

  public function postUp()
  {
    $stmt = Doctrine::getTable('Agency')->getConnection()->prepare('
      update agency set type = ? where id >= ?
    ');
    $stmt->execute(array('bti', 230));
    $stmt->closeCursor();
  }

	public function down()
	{
    $this->removeColumn('agency', 'type');
	}
}