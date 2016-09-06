<?php

class InsertUserGroup134Migration extends Doctrine_Migration
{
	public function up()
	{
    $group = new UserGroup();
    $group->fromArray(array(
      'id'          => UserGroup::PARTNERS_ID,
      'name'        => 'Партнеры',
      'credentials' => array(),
    ));
    $group->save();
	}

	public function down()
	{
    Doctrine::getTable('UserGroup')->createQuery()
      ->where('id = ?', UserGroup::PARTNERS_ID)
      ->delete()
      ->execute();
	}
}