<?php

/**
 * statistic actions.
 *
 * @package    domus
 * @subpackage statistic
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class statisticActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $filterForm = new StatisticFilterForm();

    if ($request->isMethod('post') && $request->hasParameter('filter')) {
      $this->forward404Unless($request->isXmlHttpRequest());

      $result = array();
      $filter = array();
      $filterForm->bind($request->getParameter('filter'));
      foreach ($filterForm->getValues() as $name => $value) {
        if ($value) {
          $filter[$name] = $value;
        }
      }

      // set defaults
      list($from, $to) = array(strtotime($filter['date_from']), strtotime($filter['date_to']));
      if ($from && $to && $from <= $to) {
        for ($i = $from; $i <= $to; $i += 3600 * 24) {
          $date = date('Y-m-d', $i);
          $result[$date] = array(
            'date'      =>  $date,
            'active'    =>  0,
            'added'     =>  0,
            'inactive'  =>  0,
          );
        }
      }

      $query = Doctrine::getTable('LotStatistic')->createQueryStatistic('l', $filter);

      foreach ($query->execute() as $data) {
        if ($row = $this->prepareStatisticRow($data)) {
          $result[$row['date']] = $row;
        }
      }

      //check future deleted
      if (strtotime($filter['date_to']) > strtotime(date('Y-m-d'))) {
        $query = Doctrine::getTable('Lot')->createQueryStatistic('l', $filter);

        foreach ($query->execute() as $data) {
          if ($row = $this->prepareStatisticRow($data)) {
            $result[$row['date']] = $row;
          }
        }
      }

      return $this->renderText(json_encode(array_values($result)));
    }
    else {
      $filterForm->getDefaults();
    }

    $this->filterForm = $filterForm;
  }

  protected function prepareStatisticRow($data)
  {
    if ($data instanceOf LotStatistic || $data instanceOf Lot) {
      return array(
        'date'      =>  $data->stat_at,
        'active'    =>  (!empty($data->active)) ? $data->active : 0,
        'added'     =>  (!empty($data->new)) ? $data->new : 0,
        'inactive'  =>  (!empty($data->deleted)) ? (-1 * $data->deleted) : 0
      );
    }
    return false;
  }
}
