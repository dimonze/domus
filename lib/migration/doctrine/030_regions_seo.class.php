<?php
/**
* @see #2281
*/
class RegionDescription1Migration extends Doctrine_Migration
{
  public function up()
  {
    $this->addColumn('region', 'description', 'array');
  }

  public function down()
  { }
}