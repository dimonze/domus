<?php

/**
 * region components
 *
 * @package    domus
 * @subpackage region
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class regionComponents extends sfComponents
{

  public function executeMenu()
  { 
    $this->regions = Doctrine::getTable('Region')
      ->createQuery('r')
      ->andWhere('r.in_menu = ?', true)
      ->orWhere('r.id = ?', $this->getUser()->current_region->id)
      ->groupBy('r.id')
      ->execute();
    
    $lots = sfConfig::get('lots_in_region_count');
    if ($lots) {
      $types = sfConfig::get('app_lot_types');
      $type = $this->getRequest()->getParameter('current_type');
      $this->lots_in_region = 'Объявлений ' . $types[$type]['anchor'] . ' ' . $lots;
    }
  }

  public function executeHomepage() {
    $this->region = Doctrine::getTable('Region')->find($this->region_id);
    
    if(sfConfig::get('is_new_building') || sfConfig::get('is_cottage')) {
      $subdomain = sfConfig::get('is_new_building') ? 'novostroyki' : 'cottage';
      
      $meta = sfYaml::load(sfConfig::get('sf_apps_dir') . '/../config/meta.yml');
      if(!empty($meta['all']['subdomains'][$subdomain]['subdescription'])) 
        $this->region->seotext = $meta['all']['subdomains'][$subdomain]['subdescription'];
    }
    
    if(empty($this->region->seotext)) {
      $this->getContext()->getConfiguration()->loadHelpers('Partial');
      $this->region->seotext = get_partial('region/homepage_' . $this->region_id);
    }
  }
}
