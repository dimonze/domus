<?php
class AddRayonAndShosseSeoTextsToRegionMigrationVersion179 extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->addColumn('region', 'rajontext', 'string', '5000', array(
             ));
        $this->addColumn('region', 'shossetext', 'string', '5000', array(
             ));
    }

    public function down()
    {
        $this->removeColumn('region', 'rajontext');
        $this->removeColumn('region', 'shossetext');
    }
}