<?php
/**
* @see #2281
*/
class RegionNodeDescription1Migration extends Doctrine_Migration
{
  public function up()
  {
    $this->addColumn('regionnode', 'description', 'array');
  }

  public function down()
  { }
}