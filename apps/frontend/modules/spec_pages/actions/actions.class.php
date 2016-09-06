<?php

/**
 * spec_pages actions.
 *
 * @package    domus
 * @subpackage spec_pages
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class spec_pagesActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeShow(sfWebRequest $request)
  {
    $this->page = Doctrine::getTable('SpecPages')->findOneById($request->getParameter('id'));
    $this->forward404Unless($this->page);
    sfConfig::set('homepage', true);
    sfConfig::set('all_banners', true);
  }

  public function executeSpecialTargeting(sfWebRequest $request)
  {
    $this->forward404Unless(
      $this->page = Doctrine::getTable('SpecialTargeting')->findOneBySlug($request->getParameter('slug'))
    );
    sfConfig::set('homepage', true);
    sfConfig::set('all_banners', true);
    sfConfig::set('no_top_spec_banners', true);
  }
}
