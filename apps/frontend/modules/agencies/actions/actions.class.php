<?php

/**
 * agencies actions.
 *
 * @package    domus
 * @subpackage agencies
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class agenciesActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $query = Doctrine::getTable('Agency')->createQuery()
      ->andWhere('region_id = ?', $request->getParameter('region_id'))
      ->andWhere('type = ?', $request->getParameter('type'))
      ->orderBy('name');
    $this->pager = new sfDoctrinePager('Agency', 10);
    $this->pager->setQuery($query);
    $this->pager->setPage($request->getParameter('page', 1));
    $this->pager->init();

    // SEO
    $this->h1_title = "";

    if('bti' == $request->getParameter('type')) {
      $this->h1_title = 'БТИ ' . $this->getUser()->current_region->name;
    } else {
      $this->h1_title = 'Агентства недвижимости ' . $this->getUser()->current_region->name;
    }

  }

  public function executeShow(sfWebRequest $request)
  {
    $this->agency = Doctrine::getTable('Agency')->find($request->getParameter('id'));
    $this->forward404Unless($this->agency);
    
    /**
     * 
     * SEO 
     *
     */
    
    $agency = $this->agency;
    $name = $agency->name;
    $addr = "";
    $phone = "";
    $region = "";

    if ($agency->address) {
      $addr = ', '.$agency->address;
    }
    if ($agency->phones) {
      $phone = ', '.$agency->phones[0];
    }
    if ($agency->Region) {
      $region = $agency->Region;
    }

    if ('bti' == $agency->type) {
      // title
      $this->title = sprintf('%s%s%s - mesto.ru', $name, $phone, $addr);
      // desctription
      $this->description = sprintf('Телефон и адрес %s в справочнике портала mesto.ru', $name);
      // keywords
      $this->keywords = sprintf('%s%s%s', $name, $addr, $phone);
    } else {
      // title
      $this->title = sprintf('Агентство недвижимости %s в %s, адрес и телефон, информация о компании – mesto.ru.', $name, $region);
      // desctription
      $this->description = sprintf('Описание агентства недвижимости %s в %s, адрес и телефон Агентство недвижимости %s в %s, адрес и телефон, информация о компании – на портале mesto.ru.', $name, $region, $name, $region);
      // keywords
      $this->keywords = sprintf('Агентство недвижимости %s, адрес %s, телефон %s, информация о компании %s, контакты %s.', $name, $name, $name, $name, $name);
    }
  
  }
}