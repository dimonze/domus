<?php

class CollectRegionNodeStatisticWorker extends sfGearmanWorker
{
  public
    $name = 'collect_geo_statistic',
    $methods = array(
      'collect_region_statistic', 'collect_regionnode_statistic'
    );

  protected function configure()
  {
    ini_set('memory_limit', '128M');

    $_SERVER['HTTP_HOST'] = sfConfig::get('app_site');
    $_SERVER['SCRIPT_NAME'] = '';

    $this->_configuration->loadHelpers(array('Url','Domus'));
    sfContext::createInstance($this->_configuration);
  }

  protected function write($fh, $data)
  {
    flock($fh, LOCK_EX);
    fputcsv($fh, $data, ';', '"');
    flock($fh, LOCK_UN);
  }

  protected function url(array $params)
  {
    $params['l'] = 'form';
    return sfContext::getInstance()->getController()->genUrl(
      DomusSearchRoute::buildRouteFromParams($params), true
    );
  }


  public function doCollectRegionStatistic(GearmanJob $job)
  {
    $this->startJob();
    $options = unserialize($job->workload());

    $region = Doctrine::getTable('Region')->find($options['region_id']);
    if ($region) {
      $conn = Doctrine::getTable('Lot')->getConnection();

      $lots_nb = $conn->prepare('select l.type as type, count(l.id) as lots from lot l
        left join user u on l.user_id = u.id
        where l.region_id = ? and l.status = ?
        and (u.deleted_at = ? or u.deleted_at IS NULL) and (u.inactive = ? or u.inactive IS NULL)
        group by l.type');

      $lots_nb->execute(array($region->id, 'active', 0,0));

      $file_name = sfConfig::get('sf_data_dir') . '/' . $options['file_name'] . '.csv';
      $fh = fopen($file_name, 'a');

      $result = array();
      $types  = '';
      $result['region'] = $region->name;
      $result['region_node'] = '';
      $result['city_region'] = '';

      $statistic = $lots_nb->fetchAll(Doctrine::FETCH_ASSOC);

      if (count($statistic)) {
        foreach ($statistic as $type_stat) {
          if ($type_stat['type'] == 'new_building-sale') continue;
          $type_stat['url'] = $this->url(array(
            'type'      =>  $type_stat['type'],
            'region_id' =>  $region->id
          ));
          $type = $type_stat['type'];
          $type_stat['type'] = Lot::$type_ru[$type];

          $diff_types[] = $type;

          $this->write($fh, array_merge($result, $type_stat));
        }

        $diff = array_diff(array_keys(Lot::$types), $diff_types);

        foreach($diff as $type) {
          if ($type == 'new_building-sale') continue;
          $type_stat = array();
          $type_stat['type'] = Lot::$type_ru[$type];
          $type_stat['lots'] = 0;
          $type_stat['url'] = $this->url(array(
            'type'      =>  $type,
            'region_id' =>  $region->id
          ));

          $this->write($fh, array_merge($result, $type_stat));
        }
      }
      else {
        foreach (Lot::$types as $type => $name) {
          if ($type == 'new_building-sale') continue;
          $no_lots = array();
          $no_lots['type']  = Lot::$type_ru[$type];
          $no_lots['lots']  = 0;
          $no_lots['url']   = $this->url(array(
            'type'      =>  $type,
            'region_id' =>  $region->id
          ));

          $this->write($fh, array_merge($result, $no_lots));
        }
      }

      $this->completeJob($job, 'region ' . $options['region_id'] . ' complete');
    }
    else {
      $this->completeJob($job);
    }
  }

  public function doCollectRegionnodeStatistic(GearmanJob $job)
  {
    $this->startJob();
    $options = unserialize($job->workload());

    $region = Doctrine::getTable('Region')->find($options['region_id']);
    $regionnode = Doctrine::getTable('Regionnode')->find($options['node_id']);
    if ($region && $regionnode) {
      $conn = Doctrine::getTable('Lot')->getConnection();

      $lots_nb = $conn->prepare("select l.type as type, count(l.id) as lots from lot l
        left join user u on l.user_id = u.id
        where l.region_id = ? and l.status = ?
        and (u.deleted_at = ? or u.deleted_at IS NULL) and (u.inactive = ? or u.inactive IS NULL)
        and l.address_info regexp '.*\"region_node\";a:[0-9].*;s:[0-9]+:\"" . $regionnode->id . "\".*\"city_region\".*'
        group by l.type");

      $lots_nb->execute(array($region->id, 'active', 0,0));

      $file_name = sfConfig::get('sf_data_dir') . '/' . $options['file_name'] . '.csv';
      $fh = fopen($file_name, 'a');

      $result = array();
      $types  = '';
      $result['region'] = $region->name;
      $result['region_node'] = '';
      if ($node = $regionnode->Regionnode) {
        $result['region_node'] .= $node->full_name . ', ';
      }

      if (!$regionnode->has_street && $regionnode->has_children) {
        $result['region_node'] .= $regionnode->full_name;
      }
      else {
        $city_region = $regionnode->full_name;
        $result['region_node'] = preg_replace('/, $/', '', $result['region_node']);
      }
      if (!empty($city_region)) {
        $result['city_region']  = $city_region;
      }
      else {
        $result['city_region']  = '';
      }

      $node_statistic = $lots_nb->fetchAll(Doctrine::FETCH_ASSOC);
      if (count($node_statistic)) {
        foreach ($node_statistic as $type_stat) {
          if ($type_stat['type'] == 'new_building-sale') continue;
          $type_stat['url'] = $this->url(array(
            'type'       => $type_stat['type'],
            'region_id'  => $region->id,
            'regionnode' => array($regionnode->full_name),
          ));

          $type = $type_stat['type'];
          $type_stat['type'] = Lot::$type_ru[$type];

          $diff_types[] = $type;

          $this->write($fh, array_merge($result, $type_stat));
        }
      }
      else {
        foreach (Lot::$types as $type => $name) {
          if ($type == 'new_building-sale') continue;
          $no_lots = array();
          $no_lots['type']  = Lot::$type_ru[$type];
          $no_lots['lots']  = 0;
          $no_lots['url']   = $this->url(array(
            'type'       => $type,
            'region_id'  => $region->id,
            'regionnode' => array($regionnode->full_name)
          ));

          $this->write($fh, array_merge($result, $no_lots));
        }
      }

      if ($childs = $regionnode->Nodes) {
        if (count($childs)) {
          echo 'Send task to collect children' . PHP_EOL;
          foreach ($childs as $node) {
            sfGearmanProxy::doBackground(
              'collect_regionnode_statistic',
              array('region_id' => $region->id ,'node_id' => $node->id, 'file_name' => $options['file_name'])
            );
          }
        }
      }
    }

    $this->completeJob($job, 'Region: ' . $region->id . 'RegionNode: ' . $regionnode->id . ' COLLECT');
  }
}