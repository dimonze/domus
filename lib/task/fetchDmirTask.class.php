<?php
/**
 * Fetch lots from dmir
 *
 * @package    domus
 * @subpackage task
 */
class fetchDmirTask extends sfBaseTask
{
  protected
    $config = null;

  private
    $price1 = null,
    $price2 = null,

    $settings = array(
      'apartament-sale' => array(
        0 => array(
          'url' => 'http://realty.dmir.ru/realty/sale/ru/msk/city/room/?pf=900&pt=2000&sf=8&csort=date&page=1',
          'data' => array('region_id' => 77, 'user_id' => 3, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната'))
        ),
        1 => array(
          'url' => 'http://realty.dmir.ru/realty/sale/ru/msk/city/room/?pf=2001&pt=2300&sf=8&csort=date&page=1',
          'data' => array('region_id' => 77, 'user_id' => 3, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната'))
        ),
        2 => array(
          'url' => 'http://realty.dmir.ru/realty/sale/ru/msk/city/room/?pf=2301&pt=2500&sf=8&csort=date&page=1',
          'data' => array('region_id' => 77, 'user_id' => 3, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната'))
        ),
        3 => array(
          'url' => 'http://realty.dmir.ru/realty/sale/ru/msk/city/room/?pf=2501&pt=3000&sf=8&csort=date&page=1',
          'data' => array('region_id' => 77, 'user_id' => 3, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната'))
        ),
        4 => array(
          'url' => 'http://realty.dmir.ru/realty/sale/ru/msk/city/room/?pf=3001&sf=8&csort=date&page=1',
          'data' => array('region_id' => 77, 'user_id' => 3, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната'))
        ),
        5 => array(
          'url' => 'http://realty.dmir.ru/realty/sale/ru/mo/city/room/?pf=301&pt=900&sf=8&csort=date&page=1',
          'data' => array('region_id' => 50, 'user_id' => 3, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната'))
        ),
        6 => array(
          'url' => 'http://realty.dmir.ru/realty/sale/ru/mo/city/room/?pf=901&pt=1200&sf=8&csort=date&page=1',
          'data' => array('region_id' => 50, 'user_id' => 3, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната'))
        ),
        7 => array(
          'url' => 'http://realty.dmir.ru/realty/sale/ru/mo/city/room/?pf=1201&pt=1700&sf=8&csort=date&page=1',
          'data' => array('region_id' => 50, 'user_id' => 3, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната'))
        ),
        8 => array(
          'url' => 'http://realty.dmir.ru/realty/sale/ru/mo/city/room/?pf=1701&pt=&sf=8&csort=date&page=1',
          'data' => array('region_id' => 50, 'user_id' => 3, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната'))
        ),
        9 => array(
          'url' => 'http://realty.dmir.ru/realty/sale/ru/spb/city/room/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 78, 'user_id' => 3, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната'))
        ),
        10 => array(
          'url' => 'http://realty.dmir.ru/realty/sale/ru/lo/city/room/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 47, 'user_id' => 3, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната'))
        ),
        11 => array(
          'url' => 'http://realty.dmir.ru/realty/sale/ru/spb/city/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 78, 'user_id' => 3, 'type' => 'apartament-sale')
        ),
        12 => array(
          'url' => 'http://realty.dmir.ru/realty/sale/ru/lo/city/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 47, 'user_id' => 3, 'type' => 'apartament-sale')
        ),
        13 => array(
          'url' => 'http://realty.dmir.ru/realty/sale/ru/ngr/city/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 52, 'user_id' => 3, 'type' => 'apartament-sale')
        ),
        14 => array(
          'url' => 'http://realty.dmir.ru/realty/sale/ru/ngr/city/room/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 52, 'user_id' => 3, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната'))
        ),
        15 => array(
          'url' => 'http://realty.dmir.ru/realty/sale/ru/kln/city/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 39, 'user_id' => 3, 'type' => 'apartament-sale')
        ),
        16 => array(
          'url' => 'http://realty.dmir.ru/realty/sale/ru/kln/city/room/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 39, 'user_id' => 3, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната'))
        ),
        17 => array(
          'url' => 'http://realty.dmir.ru/realty/sale/ru/krd/city/room/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 23, 'user_id' => 3, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната'))
        ),
        18 => array(
          'url' => 'http://realty.dmir.ru/realty/sale/ru/vgd/city/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 34, 'user_id' => 3, 'type' => 'apartament-sale')
        ),
        19 => array(
          'url' => 'http://realty.dmir.ru/realty/sale/ru/vgd/city/room/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 34, 'user_id' => 3, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната'))
        ),
        20 => array(
          'url' => 'http://realty.dmir.ru/realty/sale/ru/ros/city/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 61, 'user_id' => 3, 'type' => 'apartament-sale')
        ),
        21 => array(
          'url' => 'http://realty.dmir.ru/realty/sale/ru/ros/city/room/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 61, 'user_id' => 3, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната'))
        ),
      ),
      'apartament-rent' => array(
        0 => array(
          'url' => 'http://realty.dmir.ru/realty/rent/ru/msk/city/room/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 77, 'user_id' => 3, 'type' => 'apartament-rent', 'params' => array('Тип предложения' => 'комната'))
        ),
        1 => array(
          'url' => 'http://realty.dmir.ru/realty/rent/ru/mo/city/room/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 50, 'user_id' => 3, 'type' => 'apartament-rent', 'params' => array('Тип предложения' => 'комната'))
        ),
        2 => array(
          'url' => 'http://realty.dmir.ru/realty/rent/ru/spb/city/room/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 78, 'user_id' => 3, 'type' => 'apartament-rent', 'params' => array('Тип предложения' => 'комната'))
        ),
        3 => array(
          'url' => 'http://realty.dmir.ru/realty/rent/ru/spb/city/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 78, 'user_id' => 3, 'type' => 'apartament-rent')
        ),
        4 => array(
          'url' => 'http://realty.dmir.ru/realty/rent/ru/lo/city/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 47, 'user_id' => 3, 'type' => 'apartament-rent')
        ),
        5 => array(
          'url' => 'http://realty.dmir.ru/realty/rent/ru/lo/city/room/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 47, 'user_id' => 3, 'type' => 'apartament-rent', 'params' => array('Тип предложения' => 'комната'))
        ),
        6 => array(
          'url' => 'http://realty.dmir.ru/realty/rent/ru/ngr/city/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 52, 'user_id' => 3, 'type' => 'apartament-rent')
        ),
        7 => array(
          'url' => 'http://realty.dmir.ru/realty/rent/ru/ngr/city/room/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 52, 'user_id' => 3, 'type' => 'apartament-rent', 'params' => array('Тип предложения' => 'комната'))
        ),
        8 => array(
          'url' => 'http://realty.dmir.ru/realty/rent/ru/kln/city/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 39, 'user_id' => 3, 'type' => 'apartament-rent')
        ),
        9 => array(
          'url' => 'http://realty.dmir.ru/realty/rent/ru/kln/city/room/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 39, 'user_id' => 3, 'type' => 'apartament-rent', 'params' => array('Тип предложения' => 'комната'))
        ),
        10 => array(
          'url' => 'http://realty.dmir.ru/realty/rent/ru/krd/city/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 23, 'user_id' => 3, 'type' => 'apartament-rent')
        ),
        11 => array(
          'url' => 'http://realty.dmir.ru/realty/rent/ru/krd/city/room/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 23, 'user_id' => 3, 'type' => 'apartament-rent', 'params' => array('Тип предложения' => 'комната'))
        ),
        12 => array(
          'url' => 'http://realty.dmir.ru/realty/rent/ru/vgd/city/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 34, 'user_id' => 3, 'type' => 'apartament-rent')
        ),
        13 => array(
          'url' => 'http://realty.dmir.ru/realty/rent/ru/vgd/city/room/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 34, 'user_id' => 3, 'type' => 'apartament-rent', 'params' => array('Тип предложения' => 'комната'))
        ),
        14 => array(
          'url' => 'http://realty.dmir.ru/realty/rent/ru/ros/city/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 61, 'user_id' => 3, 'type' => 'apartament-rent')
        ),
        15 => array(
          'url' => 'http://realty.dmir.ru/realty/rent/ru/ros/city/room/?sf=8&csort=date&page=1',
          'data' => array('region_id' => 61, 'user_id' => 3, 'type' => 'apartament-rent', 'params' => array('Тип предложения' => 'комната'))
        ),
      ),
      'house-sale'  => array(
        0 => array(
          'url'   => 'http://realty.dmir.ru/realty/sale/ru/spb/country/?sf=8&csort=date&page=1',
          'data'  => array('region_id' => 78, 'user_id' => 3, 'type' => 'house-sale')
        ),
        1 => array(
          'url'   => 'http://realty.dmir.ru/realty/sale/ru/lo/country/?sf=8&csort=date&page=1',
          'data'  => array('region_id' => 47, 'user_id' => 3, 'type' => 'house-sale')
        ),
        2 => array(
          'url'   => 'http://realty.dmir.ru/realty/sale/ru/ngr/country/?sf=8&csort=date&page=1',
          'data'  => array('region_id' => 52, 'user_id' => 3, 'type' => 'house-sale')
        ),
        3 => array(
          'url'   => 'http://realty.dmir.ru/realty/sale/ru/kln/country/?sf=8&csort=date&page=1',
          'data'  => array('region_id' => 39, 'user_id' => 3, 'type' => 'house-sale')
        ),
        4 => array(
          'url'   => 'http://realty.dmir.ru/realty/sale/ru/vgd/country/?sf=8&csort=date&page=1',
          'data'  => array('region_id' => 34, 'user_id' => 3, 'type' => 'house-sale')
        ),
        5 => array(
          'url'   => 'http://realty.dmir.ru/realty/sale/ru/ros/country/?sf=8&csort=date&page=1',
          'data'  => array('region_id' => 61, 'user_id' => 3, 'type' => 'house-sale')
        ),
      ),
      'house-rent'  => array(
        0 => array(
          'url'   => 'http://realty.dmir.ru/realty/rent/ru/msk/country/?sf=8&csort=date&page=1',
          'data'  => array('region_id' => 77, 'user_id' => 3, 'type' => 'house-rent')
        ),
        1 => array(
          'url'   => 'http://realty.dmir.ru/realty/rent/ru/spb/country/?sf=8&csort=date&page=1',
          'data'  => array('region_id' => 78, 'user_id' => 3, 'type' => 'house-rent')
        ),
        2 => array(
          'url'   => 'http://realty.dmir.ru/realty/rent/ru/lo/country/?sf=8&csort=date&page=1',
          'data'  => array('region_id' => 47, 'user_id' => 3, 'type' => 'house-rent')
        ),
        3 => array(
          'url'   => 'http://realty.dmir.ru/realty/rent/ru/ngr/country/?sf=8&csort=date&page=1',
          'data'  => array('region_id' => 52, 'user_id' => 3, 'type' => 'house-rent')
        ),
        4 => array(
          'url'   => 'http://realty.dmir.ru/realty/rent/ru/kln/country/?sf=8&csort=date&page=1',
          'data'  => array('region_id' => 39, 'user_id' => 3, 'type' => 'house-rent')
        ),
        5 => array(
          'url'   => 'http://realty.dmir.ru/realty/rent/ru/krd/country/?sf=8&csort=date&page=1',
          'data'  => array('region_id' => 23, 'user_id' => 3, 'type' => 'house-rent')
        ),
        6 => array(
          'url'   => 'http://realty.dmir.ru/realty/rent/ru/vgd/country/?sf=8&csort=date&page=1',
          'data'  => array('region_id' => 34, 'user_id' => 3, 'type' => 'house-rent')
        ),
        7 => array(
          'url'   => 'http://realty.dmir.ru/realty/rent/ru/ros/country/?sf=8&csort=date&page=1',
          'data'  => array('region_id' => 61, 'user_id' => 3, 'type' => 'house-rent')
        ),
      ),
      'commercial-sale' => array(
        0 => array(
          'url'   => 'http://realty.dmir.ru/realty/sale/ru/msk/lands/commland/?csort=date&page=1',
          'data'  => array('region_id' => 77, 'user_id' => 3, 'type' => 'commercial-sale')
        ),
        1 => array(
          'url'   => 'http://realty.dmir.ru/realty/sale/ru/mo/lands/commland/?csort=date&page=1',
          'data'  => array('region_id' => 50, 'user_id' => 3, 'type' => 'commercial-sale')
        ),
        3 => array(
          'url'   => 'http://realty.dmir.ru/realty/sale/ru/spb/commerce/?sf=8&csort=date&page=1',
          'data'  => array('region_id' => 78, 'user_id' => 3, 'type' => 'commercial-sale')
        ),
        4 => array(
          'url'   => 'http://realty.dmir.ru/realty/sale/ru/spb/lands/commland/?csort=date&page=1',
          'data'  => array('region_id' => 78, 'user_id' => 3, 'type' => 'commercial-sale')
        ),
        5 => array(
          'url'   => 'http://realty.dmir.ru/realty/sale/ru/lo/commerce/?sf=8&csort=date&page=1',
          'data'  => array('region_id' => 47, 'user_id' => 3, 'type' => 'commercial-sale')
        ),
        6 => array(
          'url'   => 'http://realty.dmir.ru/realty/sale/ru/lo/lands/commland/?csort=date&page=1',
          'data'  => array('region_id' => 47, 'user_id' => 3, 'type' => 'commercial-sale')
        ),
        7 => array(
          'url'   => 'http://realty.dmir.ru/realty/sale/ru/ngr/commerce/?sf=8&csort=date&page=1',
          'data'  => array('region_id' => 52, 'user_id' => 3, 'type' => 'commercial-sale')
        ),
        8 => array(
          'url'   => 'http://realty.dmir.ru/realty/sale/ru/ngr/lands/commland/?csort=date&page=1',
          'data'  => array('region_id' => 52, 'user_id' => 3, 'type' => 'commercial-sale')
        ),
        9 => array(
          'url'   => 'http://realty.dmir.ru/realty/sale/ru/kln/commerce/?csort=date&page=1',
          'data'  => array('region_id' => 39, 'user_id' => 3, 'type' => 'commercial-sale')
        ),
        10 => array(
          'url'   => 'http://realty.dmir.ru/realty/sale/ru/kln/lands/commland/?csort=date&page=1',
          'data'  => array('region_id' => 39, 'user_id' => 3, 'type' => 'commercial-sale')
        ),
        11 => array(
          'url'   => 'http://realty.dmir.ru/realty/sale/ru/krd/commerce/?sf=8&csort=date&page=1',
          'data'  => array('region_id' => 23, 'user_id' => 3, 'type' => 'commercial-sale')
        ),
        12 => array(
          'url'   => 'http://realty.dmir.ru/realty/sale/ru/krd/lands/commland/?csort=date&page=1',
          'data'  => array('region_id' => 23, 'user_id' => 3, 'type' => 'commercial-sale')
        ),
        13 => array(
          'url'   => 'http://realty.dmir.ru/realty/sale/ru/vgd/commerce/?sf=8&csort=date&page=1',
          'data'  => array('region_id' => 34, 'user_id' => 3, 'type' => 'commercial-sale')
        ),
        14 => array(
          'url'   => 'http://realty.dmir.ru/realty/sale/ru/vgd/lands/commland/?csort=date&page=1',
          'data'  => array('region_id' => 34, 'user_id' => 3, 'type' => 'commercial-sale')
        ),
        15 => array(
          'url'   => 'http://realty.dmir.ru/realty/sale/ru/ros/commerce/?sf=8&csort=date&page=1',
          'data'  => array('region_id' => 61, 'user_id' => 3, 'type' => 'commercial-sale')
        ),
        16 => array(
          'url'   => 'http://realty.dmir.ru/realty/sale/ru/ros/lands/commland/?csort=date&page=1',
          'data'  => array('region_id' => 61, 'user_id' => 3, 'type' => 'commercial-sale')
        ),
      ),
    );


  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->namespace = 'fetch';
    $this->name = 'dmir';
    $this->briefDescription = null;
    $this->detailedDescription = null;

    $this->addOption('type', null, sfCommandOption::PARAMETER_REQUIRED, 'like apartament-sale', 'apartament-sale');
    $this->addOption('worker', null, sfCommandOption::PARAMETER_REQUIRED, 'worker #', null);
    $this->addOption('limit', null, sfCommandOption::PARAMETER_OPTIONAL, 'limit lots count', 150);
    $this->addOption('region_id', null, sfCommandOption::PARAMETER_OPTIONAL, 'region number or 0', 0);
    $this->addOption('price1', null, sfCommandOption::PARAMETER_OPTIONAL, 'start price', 0);
    $this->addOption('price2', null, sfCommandOption::PARAMETER_OPTIONAL, 'end price', 0);
  }


  protected function generateLink($options, $worker)
  {
    $regions = array(
      77 => array('msk', 'moskve'),
      50 => array('mo',  'moskovskoy-oblasti'),
      78 => array('spb', 'sankt-peterburge'),
      47 => array('lo',  'leningradskoy-oblasti'),
      52 => array('ngr', 'nizhegorodskoy-oblasti'),
      39 => array('kln', 'kaliningradeskoy-oblasti'),
      23 => array('krd', 'krasnodarskom-krae'),
      34 => array('vgd', 'volgogradskoy-oblasti'),
      61 => array('ros', 'rostovskoy-oblasti'),
    );

    switch ($options['type']) {
      case 'apartament-sale':
        if (!in_array($options['region_id'], array(77,50,23)))
          return $this->getLinkFromSettings($options, $worker);

        $increment  = $options['region_id'] == 77 ? 200000 : 500000;
        $max_price  = 20000000;

        $oper = 'sale';
        $offer_type = 'prodazha-kvartir';
        break;

      case 'apartament-rent':
        if (!in_array($options['region_id'], array(77,50)))
          return $this->getLinkFromSettings($options, $worker);

        $increment  = $options['region_id'] == 77 ? 5000 : 10000;
        $max_price  = 100000;

        $oper = 'rent';
        $offer_type = 'arenda-kvartir';
        break;

      case 'house-sale':
        if (!in_array($options['region_id'], array(77,50,23)))
          return $this->getLinkFromSettings($options, $worker);

        $increment  = 500000;
        $max_price  = 50000000;

        $oper = 'sale';
        $offer_type = 'prodazha-domov';
        break;

      case 'house-rent':
        if (!in_array($options['region_id'], array(50)))
          return $this->getLinkFromSettings($options, $worker);

        $increment  = 20000;
        $max_price  = 500000;

        $oper = 'rent';
        $offer_type = 'arenda-domov';
        break;

      case 'commercial-sale':
        if (!in_array($options['region_id'], array(77,50)))
          return $this->getLinkFromSettings($options, $worker);

        $increment  = 1000000;
        $max_price  = 100000000;

        $oper = 'sale';
        $offer_type = 'commerce';
        break;

      default:
        return $this->getLinkFromSettings($options, $worker);
    }

    if (!isset($regions[$options['region_id']], $increment, $max_price, $offer_type, $oper)) return false;

    $price1 = $worker*$increment;
    $price2 = $price1+($increment-1);

    $minprice = ParseTools::getMinPrice($options['type'], $options['region_id']);
    if ($price2 < $minprice) {
      return false;
    } elseif ($price1 < $minprice) {
      $price1 = $minprice;
    } elseif ($price1 > $max_price) {
      return 8;
    }

    if (!is_null($this->price1)) {
      if ($price1 < $this->price1 || $price1 > $this->price2) return false;
    }

    $link = sprintf('http://realty.dmir.ru/%s/%s/%s-v-%s/?pf=%d&pt=%d&sf=%d&csort=date&page=1',
                    $regions[$options['region_id']][0], $oper, $offer_type, $regions[$options['region_id']][1], $price1, $price2, ParseTools::MIN_S);

    $settings = array(
      'url'   => $link,
      'data'  => array('region_id' => $options['region_id'], 'user_id' => 3, 'type' => $options['type']),
      'limit' => $options['limit'],
    );

    return $settings;
  }

  protected function getLinkFromSettings($options, $worker)
  {
    if (!isset($this->settings[$options['type']][$worker-1])) return 8;
    if ($this->settings[$options['type']][$worker-1]['data']['region_id'] != $options['region_id']) return false;

    $settings = $this->settings[$options['type']][$worker-1];
    $settings = array_merge(array('limit' => $options['limit']), $settings);
    return $settings;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    if (empty($options['type'])) throw new Exception('Parameter "type" is required');

    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);

    ini_set('memory_limit', '768M');
    $log_options = array(
      'resource'  => 'Dmir',
      'type'      => $options['type'],
      'limit'     => $options['limit'],
    );

    if ($options['region_id'] !== 0) {
      if (!empty($options['price1']) && ctype_digit($options['price1'])) {
        $this->price1 = intval($options['price1']);
        $this->price2 = !empty($options['price2']) && ctype_digit($options['price2']) ? intval($options['price2']) : ($this->price1+5000000);
      }

      for ($i=1; $i<=100; $i++) {
        if (!$settings = $this->generateLink($options, $i)) continue;
        if ($settings == 8) return;

        $this->runFetcher($log_options, $settings);
      }
    }
    else {
      if (empty($options['worker'])) throw new Exception('Parameter "worker" is required');

      if (!empty($this->settings[$options['type']][$options['worker']-1])) {
        $settings = $this->settings[$options['type']][$options['worker']-1];
        $settings = array_merge(array('limit' => $options['limit']), $settings);

        $this->runFetcher($log_options, $settings);
      }
    }
  }


  private function runFetcher($log_options, $settings)
  {
    $log_options['page'] = $settings['url'];
    ParseLogger::initLogger($log_options);

    $fetcher = new Fetcher_Dmir($settings);
    $fetcher->get();

    ParseLogger::writeFinish($fetcher->lots_parsed, $fetcher->lots_fetched);

    unset($fetcher);
  }
}
