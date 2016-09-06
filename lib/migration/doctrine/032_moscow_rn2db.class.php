<?php
/**
* @see #2281
*/
class MoscowRn2Db1Migration extends Doctrine_Migration
{
  public function up()
  {
    $data = sfYaml::load(sfConfig::get('sf_config_dir') . '/districts.yml');
    $conn = Doctrine_Manager::getInstance()->connection();
    foreach ($data as $key => $rns){
      foreach ($rns as $rn_name => $rn_description){
        $node = new Regionnode();
        $node->fromArray(array(
          'region_id' => $key,
          'name' => $rn_name,
          'socr' => '',
          'parent' => null,
          'has_children' => 0,
          'has_street' => 0,
          'list' => 1,
          'description' => $rn_description
        ));
        $node->save();
      }
    }
  }

  public function down()
  {
    throw new Doctrine_Migration_IrreversibleMigrationException('Cannot insert rn into db');
  }
}