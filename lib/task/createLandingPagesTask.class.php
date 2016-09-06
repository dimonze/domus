<?php
/**
 * Creates landing pages from "seo_texts" and "sitemap_seo_data"
 *
 * @package    domus
 * @subpackage task
 */
class createLandingPagesTask extends sfBaseTask
{
  protected
    $file_src = null,
    $file_res = null,
    $config   = null,
    $_ssd      = array(),
    $_comm_types = null,
    $_app_types = null,
    $_house_types = null,
    $_count   = 0,
    $_gearman_client = null,
    $_defaults = array('page' => 1, 'limit' => 2);

  protected function configure()
  {

    $this->addArguments(array(
      new sfCommandArgument('region',   sfCommandArgument::REQUIRED, 'Region(s) - int or many integers separated by commas e.g. 77,78'),
    ));

    ini_set('memory_limit', '4G');
    ini_set('max_execution_time', 0);
    $this->namespace = 'seo';
    $this->name = 'createLandingPages';
    $this->briefDescription = 'Creates landing pages from "seo_texts" and "sitemap_seo_data"';
    $this->detailedDescription = '';
    $this->start = time();
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $region = explode(',', preg_replace('/[^,\d]/', '', $arguments['region']));
    gc_enable();
    $this->logSection('GC is', gc_enabled() ? 'enabled' : 'disabled');
    sleep(1);
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);
    $conn = Doctrine::getConnectionByTableName('SeoTexts');

    $this->_gearman_client = sfGearmanProxy::getClient();

    $conn->execute('Delete FROM `landing_page` WHERE `region_id` in ('. implode(',', $region).')');

    $regions = Doctrine::getTable('Region')->createQuery()
      ->andWhereIn('id', $region)
      ->execute();

    $ssd = Doctrine::getTable('SitemapSeoData')->findAll();
    foreach($ssd as $data) {
      if(!empty($data->level)) {
        $this->_ssd[$data->level][] = array(
          'id'   => $data->id,
          'link' => $data->link
        );
      }
      $data->free();
    }
    $this->_comm_types = Doctrine::getTable('FormField')->find(45)->getChoices(false);

    $this->logSection('>', 'Start process regions');
    foreach($regions as $region) {
      $this->processRegion($region);
      $this->processChildren($region);
    }
    $this->_gearman_client->runTasks();

    $this->logSection('Total', $this->_count);
  }
  
  private function processStreets($region_node) {
    $this->logSection(' <> <> ', sprintf('Start processing streets for regionnode_id=%s (%s)', $region_node->id, $region_node->name));
    $region = $region_node->Region;
    $streets =  Doctrine::getTable('Street')->createQuery()
      ->andWhere('regionnode_id = ?', array($region_node->id))
      ->execute();
    foreach($streets as $street) {
      foreach($this->_ssd['street'] as $ssd) {
 
        if(strpos($ssd['link'], '[вид недвижимости]') !== false) {
          foreach ($this->_comm_types as $commercial_type) {
            $this->makePage($region->id, $region_node->id, $street->id, $ssd['id'], $commercial_type);
            //if(rand(0,4)==0) break;
          }
        }
        else {
          $this->makePage($region->id, $region_node->id, $street->id, $ssd['id']);
        }
      }

    }
  }

  private function processChildren($region, $parent_id = null) {
    $children = Doctrine::getTable('Regionnode')->createQuery()
          ->andWhere('region_id = ? and list = ?', array($region->id, 1));
    null === $parent_id
      ? $children->andWhere('parent is null', array())
      : $children->andWhere('parent = ?', array($parent_id));


    foreach($children->execute() as $region_node) {
      switch($region_node->socr) {
        case 'м.'   : //Go next;
        case 'м'    : $type = 'street';   break; //Yea, like street.

        case ''     : //Go next;
        case 'р-н'  : //Go next
        case 'район': $type = 'district'; break;

        case 'г'    : $type = 'city';     break;
        default     : $type = 'village';     break;
      }
      if($region_node->id == $parent_id) {
        $this->log('Continue because of empty node');
        continue;
      }

      if(!isset($this->_ssd[$type])) {
        throw new Exception(sprintf('There is no "%s" type in SSD. // id: %s', $type, $region_node->id));
      }

      $this->logSection('> > >', sprintf('Start process child %s', $region_node->full_name));
      foreach($this->_ssd[$type] as $node_ssd) {
 
        if(strpos($node_ssd['link'], '[вид недвижимости]') !== false) {
          $this->logSection('> > > >', sprintf('Start process commercial types for %s', $type));
          foreach ($this->_comm_types as $commercial_type) {
            $this->makePage($region->id, $region_node->id, null, $node_ssd['id'], $commercial_type);
            //if(rand(0,4)==0) break;
          }
        }
        else{
          $this->makePage($region->id, $region_node->id, null, $node_ssd['id']);
        }
      }

      if($region_node->has_street) {
        $this->processStreets($region_node);
      }
      //Going deep!
      if($region_node->has_children) {
        $this->processChildren($region, $region_node->id);
      }
    }
  }

  private function processRegion($region) {
    foreach($this->_ssd['region'] as $ssd) {

      if(isset($ssd['link']) && strpos($ssd['link'], '[вид недвижимости]') !== false) {
        foreach ($this->_comm_types as $commercial_type) {
          $this->makePage($region->id, null, null, $ssd['id'], $commercial_type);
          //if(rand(0,4)==0) break;
        }
      } else
      $this->makePage($region->id, null, null, $ssd['id']);
    }
  }

  private function makePage($region_id, $region_node_id, $street_id, $ssd_id, $commercial_type = null) {
    $this->_gearman_client->addTask($this->_gearman_client->getMethodName('create_landing_page'), serialize(array(
      'region_id'       => $region_id,
      'region_node_id'  => $region_node_id,
      'street_id'       => $street_id,
      'ssd_id'          => $ssd_id,
      'commercial_type' => $commercial_type,
    )));
    if(0 === ++$this->_count % 10000) {
      gc_collect_cycles();
      $this->logSection('Memory', sprintf('%s MB', memory_get_usage()/1024/1024));
      $this->logSection('From start', sprintf('%s min', round((time() - $this->start) / 60, 2)));
      $this->_gearman_client->runTasks();
    }
    $this->logSection($this->_count, implode(' - ', array($region_id, $region_node_id)));
  }
}
