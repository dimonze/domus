<?php

/**
 * page actions.
 *
 * @package    domus
 * @subpackage page
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class pageActions extends sfActions
{
  public function postExecute()
  {
    MetaParse::setMetas($this);
  }
  public function executeIndex(sfWebRequest $request)
  {
    //forward 404 if is source request
    foreach (array('images', 'css', 'js', 'uploads') as $value) {
      if (preg_match('/^\/' . $value . '/', $request->getPathInfo())) {
        $this->forward404();
      }
    }

    if ($this->getRequestParameter('module') != 'page' ) {
      if ($openstat = $request->getParameter('_openstat')) {
        $this->redirect('@homepage?_openstat=' . $openstat);
      }
      $this->redirect('@homepage');
    }

    if ($request->getPathInfo() == '/') {
      if (77 != $this->getUser()->current_region->id) {
        if ($openstat = $request->getParameter('_openstat')) {
          $this->redirect('@homepage?_openstat=' . $openstat);
        }
        else {
          $this->redirect('@homepage');
        }
      }
      else {
        $this->forward('page', 'homepage');
      }
    }
    else {
      $path = substr($request->getPathInfo(), 1);
      $page = Doctrine::getTable('Page')->findOneByUrl($path);

      if (in_array($path, Lot::$directions)) {
        $lots = array();
        foreach (array_keys(sfConfig::get('app_lot_types')) as $type) {
          $lots_of_type = Lot::getLotsForDirection($path, $type);
          if (count($lots_of_type) > 0) {
            $lots[$type] = $lots_of_type;
          }
        }
        if (count($lots) > 0) {
          $this->lots = $lots;
        }
        $direction_nodes = Lot::$direction_regionnodes[$path];
        $nodes_translit = array();
        foreach ($direction_nodes as $node){
          $nodes_translit[] = str_replace(
            array_keys(DomusSearchRoute::$translit_table),
            array_values(DomusSearchRoute::$translit_table),
            $node
          );
        }

        $this->nodes_translit = $nodes_translit;
      }
    }
    $this->forward404Unless($page);
    foreach (array('title', 'name', 'description', 'keywords') as $param) {
      if($param == 'name' && strpos($page->name, '__') === 0) {
        continue;
      }
      if ($page->$param) {
        $this->getResponse()->addMeta($param, $page->$param);
      }
    }
    $request->setParameter('current_page', $page);

    $this->page = $page;
  }

  public function execute404(sfWebRequest $request)
  {}
  public function execute500(sfWebRequest $request)
  {}

  public function executeBanners(sfWebRequest $request)
  {
    sfConfig::set('homepage', true);
    sfConfig::set('all_banners', true);
  }

  public function executeXmlfields(sfWebREquest $request)
  {
    $this->page = Doctrine::getTable('Page')->findOneByUrl('xml_fields');
    $this->afields = Doctrine::getTable('FormField')
      ->createQuery('ff')
      ->leftJoin('ff.FormField f')
      ->where('ff.xml_name IS NOT NULL')
      ->andWhere('f.type LIKE ( ? )', 'apartament%')
      ->orderBy('f.position')
      ->fetchArray();
    $this->hfields = Doctrine::getTable('FormField')
        ->createQuery('ff')
      ->leftJoin('ff.FormField f')
      ->where('ff.xml_name IS NOT NULL')
      ->andWhere('f.type LIKE ( ? )', 'house%')
      ->orderBy('f.position')
      ->fetchArray();
    $this->cfields = Doctrine::getTable('FormField')
      ->createQuery('ff')
      ->leftJoin('ff.FormField f')
      ->where('ff.xml_name IS NOT NULL')
      ->andWhere('f.type LIKE ( ? )', 'commercial%')
      ->orderBy('f.position')
      ->fetchArray();
  }

  public function executeHomepage(sfWebRequest $request)
  {
    $this->cache_prefix = sprintf('homepage_%d_%d_%d_%d_',
                                  $request->getCookie('js_on'),
                                  $this->getUser()->current_region->id,
                                  sfConfig::get('is_new_building'),
                                  sfConfig::get('is_cottage'));
    sfConfig::set('homepage', true);

    if (sfConfig::get('is_new_building')) {
      $this->new_building_form = new NewbuildingSearchFilterForm();
    }
  }

  public function executePrice(sfWebRequest $request)
  {}

  public function executeOneBanner(sfWebRequest $request)
  {
    $this->id = $request->getParameter('id');
    $this->forward404Unless(
      $banner = OpenX::getOneBanner($this->id)
    );
    if(!empty($banner['htmltemplate']) && preg_match('#href="(.*?)"#', $banner['htmltemplate'], $url)) {
      $url = $url[1];
    }
    else{
      $url = $banner['url'];
    }

    $link = 'http://media.mesto.ru/www/delivery/ck.php?' . http_build_query(array(
      'oaparams'  => 2,
      'bannerid'  => $banner['ad_id'],
      'zoneid'    => $banner['zone_id'],
      'oadest'    => $url
    ), null, '__');

    if(preg_match('/x-shockwave-flash/', $banner['htmltemplate'])){
      $banner['htmltemplate'] = sprintf('<a href="" class="special-overlay"><div></div></a>%s', $banner['htmltemplate']);
    }
    $this->html = preg_replace('/href=".*?"/', 'href="' . $link . '"', $banner['htmltemplate']);
    OpenX::updateStat($this->id);
    sfConfig::set('no_top_spec_banners', true);
  }
}
