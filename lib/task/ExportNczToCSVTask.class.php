<?php

class ExportNczToCSVTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
    ));

    $this->namespace        = 'domus';
    $this->name             = 'ExportNczToCSV';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [ExportNczToCSV|INFO] task does things.
Call it with:

  [php symfony ExportNczToCSV|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine_Manager::connection();

    $file = fopen('/tmp/NCZ_regionnodes.csv', 'a');
    $this->word_combinations = sfYaml::load(sfConfig::get('sf_config_dir')
      . '/words_combinations_for_sitemap.yml');

    $nodes = $conn->prepare('
      SELECT regionnode.id FROM regionnode
      LEFT JOIN region
        ON region.id = regionnode.region_id
      WHERE regionnode.parent IS NULL
        AND regionnode.list = ?
        AND regionnode.region_id NOT IN (?, ?, ?, ?)
        AND region.in_menu = ?
      GROUP BY regionnode.id
      ORDER BY regionnode.region_id asc, regionnode.socr asc
    ');
    $nodes->execute(array(1, 50, 77, 52, 39, 1));

    while($node = $nodes->fetch()) {
      $node = Doctrine::getTable('Regionnode')->find($node['id']);
      $this->logSection('node', 'Node: ' . $node->region_id);
      $types = Doctrine::getTable('Lot')->createQuery()
        ->select('type, count(id) as count')
        ->andWhere('region_id = ?', $node->region_id)
        ->andWhere('status = ?', 'active')
        ->andWhere('address1 LIKE ?', '%' . $node->name . '%')
        ->fetchArray();
      foreach ($types as $type) {
        if ($type['count'] > 0) {
          foreach ($this->word_combinations as $combination_id => $additional) {
            foreach ($additional as $combination => $metas) {
              if ($metas['type'] == $type['type']) {
                $nchz = $combination . ' в ' . WordInflector::get($node->name, WordInflector::TYPE_PREPOSITIONAL) . PHP_EOL;
                fwrite($file, $nchz);
                $this->logSection('write', 'Write to file: ' . $nchz . ' Type: ' . $type['type']);
              }
            }
          }
        }
      }
    }

    fclose($file);
  }
}
