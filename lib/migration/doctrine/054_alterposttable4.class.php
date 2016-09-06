<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AlterPostTable4Migration extends Doctrine_Migration
{
	public function up()
	{
    echo "Add column author_name" . PHP_EOL;
    $this->addColumn('post', 'author_name', 'string', array(
      'length' => 255,
      'default' => NULL
    ));
	}

	public function down()
	{
    $this->removeColumn('post', 'author_name');
    echo "Remove column author_name" . PHP_EOL;
  }
}