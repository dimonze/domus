<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Version173 extends Doctrine_Migration_Base
{
  public function up()
  {
    $this->addColumn('lot', 'nb_banner_id', 'integer', '2', array(
       'unsigned' => '1',
       ));
  }

  public function down()
  {
    $this->removeColumn('lot', 'nb_banner_id');
  }
}
