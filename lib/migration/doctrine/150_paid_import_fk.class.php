<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class PaidImportFk150Migration extends Doctrine_Migration
{
	public function up()
	{
    $this->createForeignKey('import_order', array(
      'local' => 'user_id',
      'foreign' => 'id',
      'foreignTable' => 'user',
      'onUpdate' => NULL,
      'onDelete' => 'cascade',
      'name' => 'import_order__user_id',
    ));
    
    $this->createForeignKey('import_order_options', array(
      'local' => 'order_id',
      'foreign' => 'id',
      'foreignTable' => 'import_order',
      'onUpdate' => NULL,
      'onDelete' => 'cascade',
      'name' => 'import_order__options_order_id',
    ));
	}

	public function down()
	{
    $this->dropForeignKey('import_order_options', 'import_order__options_order_id');
    $this->dropForeignKey('import_order', 'import_order__user_id');
	}
}