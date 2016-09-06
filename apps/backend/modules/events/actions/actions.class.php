<?php

require_once dirname(__FILE__).'/../lib/eventsGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/eventsGeneratorHelper.class.php';

/**
 * events actions.
 *
 * @package    domus
 * @subpackage events
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12474 2008-10-31 10:41:27Z fabien $
 */
class eventsActions extends autoEventsActions
{
  public function executeIndex(sfWebRequest $request)
  {
    // sorting
    if ($request->getParameter('sort') && $this->isValidSortColumn($request->getParameter('sort')))
    {
      $this->setSort(array($request->getParameter('sort'), $request->getParameter('sort_type')));
    }

    // pager
    if ($request->getParameter('page'))
    {
      $this->setPage($request->getParameter('page'));
    }

    $this->setFilters(array_merge($this->getFilters(), array('post_type' => 'events')));
    $this->pager = $this->getPager();
    $this->sort = $this->getSort();
  }
}
