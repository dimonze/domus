<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AlterPostTable3Migration extends Doctrine_Migration
{
	public function up()
	{
    echo "Create Foreign Key user_id" . PHP_EOL;
    $this->createForeignKey('post', array(
      'local' => 'user_id',
      'foreign' => 'id',
      'foreignTable' => 'user',
      'onUpdate' => NULL,
      'onDelete' => 'CASCADE',
      'name' => 'post_user_id',
    ));
    echo "Create Index user_id_idx" . PHP_EOL;
    $this->addIndex('post', 'user_id', array(
      'fields' =>
        array(
          0 => 'user_id',
        )
    ));
	}

	public function down()
	{
    $this->dropForeignKey('post', 'post_user_id');
    echo "Drop Foreign key post_user_id" . PHP_EOL;
    $this->removeIndex('post', 'user_id');
    echo "Drop Index user_id_idx" . PHP_EOL;
  }
}