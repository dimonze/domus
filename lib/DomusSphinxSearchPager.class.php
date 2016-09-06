<?php
/**
 * DomusSphinxClient
 *
 * based on sfSphinxPager class of Sphinx project
 *
 * @package    symfony
 * @subpackage routing
 * @author     Cherednikov Alexey
 * @version    SVN: $Id$
 */
class DomusSphinxSearchPager extends sfSphinxDoctrinePager
{
  /**
   * Return results for given page
   * @return Doctrine_Collection
   */
  public function getResults()
  {
    $res = $this->sphinx->getRes();
    if ($res['total_found'] == 0 || empty($res['matches']))
    {
      return array();
    }
    // First we need to get the Ids
    $ids = array();
    foreach ($res['matches'] as $match)
    {
      $ids[] = $match['id'];
    }

    // Then we retrieve the objects correspoding to the found Ids
    switch ($this->class) {
      case 'Lot':
        return $this->buildLotDataResult($res);
        break;

      case 'Street':
        return $this->buildStreetsDataResult($res);
        break;

      default:
        $query = Doctrine::getTable($this->class)
          ->createQuery('p')
          ->select('p.*, FIELD(' . $this->getPkColumn() . ', ' . implode(', ', $ids) . ') post_ids' )
          ->whereIn($this->getPkColumn(), $ids)
          ->orderBy('post_ids');
        return $query->execute();
        break;
    }
  }

  //Эмуляция выдачи коллекции лотов, соответствующих запросу в виде объектов
  private function buildLotDataResult($res = array()){
    if(empty($res)) return array();

    $request = sfContext::getInstance()->getRequest();
    $currency_type = $request->getParameter('type');
    switch($currency_type){
      case 'apartament-rent':
        $currency_type = $request->getParameter('currency_type', 'month');
        break;
      case 'commercial-rent':
        $currency_type = $request->getParameter('currency_type', 'year');
        break;

      default:
        $currency_type = false;
    }
    $request->setParameter('currency_type', $currency_type);

    foreach ($res['matches'] as $k => $v) {
      $types = array_keys(Lot::$types);
      $statuses = array_keys(Lot::$statuses);
      $brief = array();

      /* LotInfo */
      $lotInfo = new Doctrine_Collection('LotInfo');
      foreach ($v['attrs'] as $field_id => $field_val) {
        if(preg_match('#f(\\d+)#', $field_id, $num)){
          $li_data = array(
              'lot_id' => $v['attrs']['lot_id'],
              'field_id' => $num[1],
              'value' => $field_val
          );

          $li = new LotInfo();
          $li->fromArray($li_data);
          $lotInfo->add($li);
        }
      }

      /* Brief */
      switch($v['attrs']['type']){
        case 1:
        case 2:
          if(isset($v['attrs']['f1'])) {
            $brief[] = 'Площадь:' . $v['attrs']['f1'] .  ' м<sup>2</sup>';
          }

          if(isset($v['attrs']['f3'])) {
            $brief[] = 'Этаж: ' . $v['attrs']['f3'] . (isset($v['attrs']['f4']) ? '/' . $v['attrs']['f4'] : '');
          }

          $rooms = "f5" . ($v['attrs']['type'] == 1 ? "4" : "5");
          if(isset($v['attrs'][$rooms])) {
            $brief[] = preg_replace('#\s*квартира\s*#', '', $v['attrs'][$rooms]);
          }
          break;

        case 3:
        case 4:
          if(isset($v['attrs']['f26'])) {
            $brief[] = 'Площадь:' . $v['attrs']['f26'] .  ' м<sup>2</sup>';
          }

          if(isset($v['attrs']['f27'])) {
            $brief[] = 'Участок:' . $v['attrs']['f27'] .  ' соток';
          }

          if(isset($v['attrs']['f4'])){
            $brief[] = 'Этажность: ' . $v['attrs']['f4'];
          }
          break;

        case 5:
        case 6:
          if(isset($v['attrs']['f46'])) {
            $brief[] = 'Площадь:' . $v['attrs']['f46'] .  ' м<sup>2</sup>';
          }

          if(isset($v['attrs']['f47'])) {
            $brief[] = 'Участок:' . $v['attrs']['f47'] .  ' га';
          }

          if(isset($v['attrs']['f45'])) {
            $brief[] = 'Тип: ' . $v['attrs']['f45'];
          }
          break;
      }
      
      $lot_data = array(
          'id' => $v['attrs']['lot_id'],
          'type' => $types[(intval($v['attrs']['type']) - 1)],
          'region_id' => $v['attrs']['region_id'],
          'address1' => $v['attrs']['address1'],
          'address2' => $v['attrs']['address2'],
          'address_info' => empty($v['attrs']['address_info']) ? null : unserialize($v['attrs']['address_info']),
          'latitude' => $v['attrs']['latitude'],
          'longitude' => $v['attrs']['longitude'],
          'images' => $v['attrs']['images'],
          'thumb' => $v['attrs']['thumb'],
          'auto_description'  =>  $v['attrs']['auto_description'],
          'created_at' => date("Y-m-d H:i:s", $v['attrs']['created_at_ts']),
          'active_till' => date("Y-m-d H:i:s", $v['attrs']['active_till_ts']),
          'updated_at' => date("Y-m-d H:i:s", $v['attrs']['updated_at_ts']),
          'status' => $statuses[(intval($v['attrs']['status']) - 1)],
          'price' => !empty($v['attrs']['formated_price']) ? $v['attrs']['formated_price'] : (!empty($v['attrs']['f70']) ? $v['attrs']['f70'] : 0),
          'currency' => 'RUR',
          'deleted' => 0,
          'brief' => implode("\n", $brief),
          'slug' => $v['attrs']['slug'],
      );

      $user_data = array(
          'id' => $v['attrs']['user_id'],
          'type' => $v['attrs']['user_type'],
          'company_name' => $v['attrs']['company_name'],

      );

      $user = new User();
      $user->fromArray($user_data);

      $lot = new Lot();
      $lot->fromArray($lot_data);
      $lot->User = $user;
      $lot->LotInfo = $lotInfo;

      $res['matches'][$k] = $lot;
    }

    return $res['matches'];
  }

  //Эмуляция выдачи коллекции улиц-объектов
  private function buildStreetsDataResult($res = array())
  {
    $result = array();
    if(empty($res) || !$res['total']) return $result;

    foreach ($res['matches'] as $street) {
      $result[] = $street['attrs']['street'];
    }

    return $result;
  }
 }
