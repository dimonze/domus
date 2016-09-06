<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AlterClaim075Migration extends Doctrine_Migration
{
	public function up()
	{
    echo "Add column user_name, user_email" . PHP_EOL;
    $this->addColumn('claim', 'user_name', 'string', array(
      'length' => '255',
      'default' => null
     ));
    $this->addColumn('claim', 'user_email', 'string', array(
      'length' => '60',
      'default' => null
     ));

     $this->changeColumn('claim', 'user_id', 'integer', array(
        'unsigned' => true,
        'notnull' => false,
        'default' => null,
        'length' => 4,
     ));
	}

	public function down()
	{
    $this->removeColumn('claim', 'user_name');
    $this->removeColumn('claim', 'user_email');

    $this->changeColumn('claim', 'user_id', 'integer', array(
        'unsigned' => true,
        'notnull' => true,
        'length' => 4,
    ));
    echo "Remove column user_name, user_email" . PHP_EOL;
	}
}