<?php
/**
 * @see #1508
 */
class AddInviteMigration extends Doctrine_Migration
{
	public function up()
	{
    $this->createTable('invite',
      array(
        'code' => array(
          'type' => 'string',
          'primary' => true,
          'length' => 12,
        ),
        'email' => array(
          'type' => 'string',
          'primary' => true,
          'length' => 50,
        ),
        'user_id' => array(
          'type' => 'integer',
          'unsigned' => true,
          'length' => 4,
        ),
        'created_at' => array(
          'type' => 'timestamp',
          'length' => 25,
        ),
      ),
      array(
        'indexes' => array(),
        'primary' => array(
          0 => 'code',
          1 => 'email',
        ),
      ));

      $this->createForeignKey('invite', array(
        'local' => 'user_id',
        'foreign' => 'id',
        'foreignTable' => 'user',
        'onUpdate' => NULL,
        'onDelete' => 'cascade',
        'name' => 'invite_user_id',
      ));
	}

	public function down()
	{
    $this->dropForeignKey('invite', 'invite_user_id');
		$this->dropTable('invite');
	}
}