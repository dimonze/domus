<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Version172 extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->addColumn('lot', 'nb_more_info_link', 'string', '500', array(
             ));
    }

    public function down()
    {
        $this->removeColumn('lot', 'nb_more_info_link');
    }
}