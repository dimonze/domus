<?php

class ExportLotsStatTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'domus';
    $this->name             = 'ExportLotsStat';    
  }

  protected function execute($arguments = array(), $options = array())
  {
    ini_set('max_execution_time', 0);
    // initialize the database connection
    
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase('doctrine')->getConnection();
    $query = $connection->prepare('SELECT * FROM street WHERE regionnode_id = ?');
    $file = fopen(sfConfig::get('sf_upload_dir') . '/' . 'lots_stat_export.' . date('d-m-Y') . '.csv', 'w');    
    
    $this->sphinx = new DomusSphinxClient(
      sfConfig::get('app_sphinx')
    );           
    $types = array_keys(Lot::$types);
    
    
    $this->logSection('1.', 'Getting regions...');
    
    $regions_temp = Doctrine::getTable('Region')->createQuery()->orderBy('id')->execute();    
    $regions = array();
    $region_nodes = array();
    foreach ($regions_temp as $region)
      $regions[$region->id] = $region;    
    unset($regions_temp);
    
    
    $this->logSection('2.', 'Getting region nodes...');
    
    $region_nodes_temp = Doctrine::getTable('Regionnode')->createQuery()->where('region_id in (77, 78, 50, 47)')->execute( array(), Doctrine::HYDRATE_ARRAY);
    
    
    $this->logSection('3.', 'Regenerating indexes...');    
    
    foreach ($region_nodes_temp as $node)
      $region_nodes[$node['id']] = $node;    
    unset($region_nodes_temp);    
    
    
    $this->logSection('4.', 'Parsing nodes tree...');
    
    foreach($region_nodes as $key=>$node) {   
      if ($node['has_children'])
        continue;
      $temp = $node;      
      $complete = array();
      while (strlen($pid = $temp['parent'])) {          
        array_unshift($complete, $temp['socr'] . ' ' . $temp['name']);        
        $temp = $region_nodes[$pid];
      }
      
      array_unshift($complete, $temp['socr'] . ' ' . $temp['name']);
      
      //$regions[$node['region_id']]->name
      $address = implode(', ', $complete); 
      $pool = array();
      if ($node['has_street']) {   
        $query->execute(array($node['id']));       
           
        while ($street = $query->fetch()) {          
          $pool[] = array(
            'q' => $address . ', ' . $street['socr'] . ' ' . $street['name']
          );
        }
          
        
      } else {
        $pool[] = array(
         'q' => $address
        );
      }
      
      foreach ($pool as $el) {
        foreach ($types as $type) {
          $params = array(
            'q' => str_replace('/', ' ', $el['q']),
            'region_id' => $node['region_id'],
            'type' => $type
          );
          
          $this->sphinx->search($params);
          $result = $this->sphinx->getRes();
          $this->sphinx->_search_query = null;
          $this->sphinx->ResetFilters();
          

          fputcsv($file, array(
            iconv("UTF-8", "cp1251", $regions[$node['region_id']]->name . ', ' . $el['q']),
            iconv("UTF-8", "cp1251", 'http://mesto.ru/' . $params['type'] . '/' . $params['region_id'] . '/search/l/form/q/' . urlencode($params['q'])),
            iconv("UTF-8", "cp1251", $result['total_found'])
          ));
          if ($result['total'] || $result['total_found']) {
            $this->logSection(
              'node #' . $node['region_id'],
              $regions[$node['region_id']]->name . ', ' . $el['q'] . ' - ' .
              (!empty($result['total_found']) ? $result['total_found'] : $result['total'])
            );
          }
          
          unset($result);
           #to kill
          
          
        }
      }
            
      unset($pool);
    }    
    
    $this->logSection('Total nodes', count($region_nodes));
    fclose($file);
    
  }
}
