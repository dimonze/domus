<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddLotTitleToLandingPage161Migration extends Doctrine_Migration_Base
{
  public function up()
  {
    $this->addColumn('landing_page', 'lot_title_prefix', 'string', '255', array());
  }

  public function down()
  {
    $this->removeColumn('landing_page', 'lot_title_prefix');
  }
}