<?php

/**
 * filter actions.
 *
 * @package    domus
 * @subpackage filter
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class filterActions extends sfActions
{
//  public function executeIndex (sfWebRequest $request)
//  {
//    $user = $this->getUser();
//
//    $filters = array();
//    $current_filter = array();
//
//    if ($user->isAuthenticated()) {
//      $filters = Doctrine::getTable('ModeratorFilters')->createQuery()
//        ->andWhere('user_id = ?', $this->getUser()->id)
//        ->fetchArray();
//    }
//
//    $data = array (
//      'filters' => &$filters,
//    );
//
//    return $this->renderText(json_encode($data));
//  }

  public function executeAdd (sfWebRequest $request)
  {
    $user = $this->getUser();
    $this->forward404Unless($request->hasParameter('filter'));

    $filter = Doctrine::getTable('ModeratorFilters')->createQuery()
      ->where('name = ?', $request->getParameter('name'))
      ->andWhere('user_id = ?', $user->id)
      ->fetchOne();
    if ($filter){
      return $this->renderText(json_encode(array('error' => 'name_exist')));
    }
    $filter = new ModeratorFilters();
    $filter->name = $request->getParameter('name');
    $filter->user_id = $user->id;
    $filter->params = $request->getParameter('filter');
    $filter->save();

    return $this->renderText(json_encode(array('id' => $filter->id, 'name' => $filter->name)));
  }

  public function executeDelete (sfWebRequest $request)
  {
    $user = $this->getUser();
    $this->forward404Unless($request->hasParameter('id'));
    
    $filter = Doctrine::getTable('ModeratorFilters')->createQuery()
                ->andWhere('id = ?', $request->getParameter('id'))
                ->andWhere('user_id = ?', $user->id)
                ->fetchOne();
    $this->forward404Unless($filter);
    $filter->delete();

    return $this->renderText(json_encode(array('success' => true)));
  }

  public function executeRename (sfWebRequest $request)
  {
    $user = $this->getUser();
    $this->forward404Unless($request->hasParameter('id'));
    $this->forward404Unless($request->hasParameter('name'));
    
    $filter = Doctrine::getTable('ModeratorFilters')->createQuery()
                ->andWhere('id = ?', $request->getParameter('id'))
                ->andWhere('user_id = ?', $user->id)
                ->fetchOne();
    $this->forward404Unless($filter);
    $filter->name = $request->getParameter('name');
    $filter->save();

    return $this->renderText(json_encode(array('success' => true)));
  }

  public function executeGet(sfWebRequest $request)
  {
    $user = $this->getUser();

    $filter = Doctrine::getTable('ModeratorFilters')->createQuery()
      ->select('params')
      ->andWhere('id = ?', $request->getParameter('id'))
      ->andWhere('user_id = ?', $user->id)
      ->fetchOne();

    return $this->renderText(json_encode(array('params' => $filter->params)));
  }
}
