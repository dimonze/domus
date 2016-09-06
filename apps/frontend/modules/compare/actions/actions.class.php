<?php

/**
 * compare actions.
 *
 * @package    domus
 * @subpackage compare
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class compareActions extends sfActions
{
  public function postExecute() {
    MetaParse::setMetas($this);
  }

  public function executeIndex(sfWebRequest $request)
  {
    $items = $this->getUser()->compareList();
    if (count($items)) {
      $this->lots = Doctrine::getTable('Lot')->createQueryActiveList()
          ->andWhereIn('Lot.id', $items)
          ->andWhere('Lot.type = ?', $request->getParameter('type'))
          ->execute();

      $params = array();
      foreach ($this->lots as $i => $lot)
      {
        foreach ($lot->getLotInfoArray() as $group)
        {
          foreach ($group as $row)
          {
            if (!isset($params[$row['name']][0]))
            {
              $params[$row['name']] = array_fill(0, count($this->lots), array(''));
            }
            
            $params[$row['name']][$i] = array($row['value'], $row['help']);
          }
        }
      }
      $this->params = $params;

      $lot_types = sfConfig::get('app_lot_types');
      if (isset($lot_types[$request->getParameter('type')]))
      {
        $type_name = $lot_types[$request->getParameter('type')]['accusative'];
      }
      else
      {
        $type_name = '';
      }
      
      $this->getContext()->getConfiguration()->loadHelpers('Word');
      $this->getResponse()->addMeta('name',
          'Список сравнения ' .
          $type_name . ' - ' .
          count($this->lots) .
          ending(count($this->lots), ' объект', ' объекта', ' объектов'));
    }
    else {
      $this->getResponse()->addMeta('name', 'Список сравнения');
    }
  }

  public function executeToggle(sfWebRequest $request)
  {
    $this->forward404Unless($request->hasParameter('id'));

    $user = $this->getUser();
    $id = $request->getParameter('id');

    if ($user->compareIsset($id)) {
      $user->compareDelete($id);
      $status = array('msg' => 'Объект был удален из списка сравнения');
    }
    else {
      $user->compareAdd($id);
      $status = array('msg' => 'Объект был добавлен в список сравнения');
    }


    if ($request->isXmlHttpRequest()) {
      $status['nb_items'] = $user->compareCount();
      return $this->renderText(json_encode($status));
    }
    else {
      $user->setFlash('compare_success', $status['msg']);
      $this->redirect($request->getReferer());
    }
  }
}
