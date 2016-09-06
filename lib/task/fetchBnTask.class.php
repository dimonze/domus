<?php
/**
 * Fetch lots from http://www.bn.ru/
 *
 * @package    domus
 * @subpackage task
 */
class fetchBnTask extends sfBaseTask
{
  protected
    $config = null;

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->namespace = 'fetch';
    $this->name = 'bn';
    $this->briefDescription = null;
    $this->detailedDescription = null;

    $this->addOption('type', null, sfCommandOption::PARAMETER_REQUIRED, 'like apartament-sale', 'apartament-sale');
    $this->addOption('worker', null, sfCommandOption::PARAMETER_REQUIRED, 'worker #', null);
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Limit lots count', 150);
    $this->addOption('region_id', null, sfCommandOption::PARAMETER_REQUIRED, 'region number or 0', 0);
  }

  protected function generateLink($options) {
    $link = false;
    $price1 = false;
    $price2 = false;
    $division = false;

    switch ($options['type']) {
      case 'apartament-sale':
        $increment  = 500;
        $division   = 1000;

        if ($options['region_id'] == 77) {
          $link = 'http://www.bn.ru/msk/flats/search/?type=1&type_id[0]=14&type_id[1]=16&price1=%d&price2=%d&so1='.ParseTools::MIN_S.'&action_id[0]=1&sortordtype=1&sorttype=0';
        } elseif ($options['region_id'] == 52) {
          $link = 'http://www.bn.ru/regions/flats/search/?form[minprice]=%d&form[maxprice]=%d&form[mintotal]='.ParseTools::MIN_S.'&form[markettypes][0]=1&form[area]=59&sortordtype=1&sorttype=1';
        } elseif ($options['region_id'] == 39) {
          $link = 'http://www.bn.ru/regions/flats/search/?form[minprice]=%d&form[maxprice]=%d&form[mintotal]='.ParseTools::MIN_S.'&form[markettypes][0]=1&form[area]=30&sortordtype=1&sorttype=1';

          $price1 = ParseTools::getMinPrice($options['type'], $options['region_id']);
          if ($division) $price1 /= $division;
          $price2 = 10000;
        }
        break;
      case 'apartament-rent':
        $increment  = 10000;

        if ($options['region_id'] == 77) {
          $link = 'http://www.bn.ru/msk/flats/search/?type=1&type_id[0]=14&type_id[1]=16&price1=%d&price2=%d&so1='.ParseTools::MIN_S.'&action_id[0]=2&sortordtype=1&sorttype=0';
        } elseif ($options['region_id'] == 52) {
          $link = 'http://www.bn.ru/regions/flats/search/?form[minprice]=%d&form[maxprice]=%d&form[mintotal]='.ParseTools::MIN_S.'&form[markettypes][0]=2&form[area]=59&sortordtype=1&sorttype=1&next=50';

          $price1 = ParseTools::getMinPrice($options['type'], $options['region_id']);
          if ($division) $price1 /= $division;
          $price2 = 300000;
        } elseif ($options['region_id'] == 39) {
          $link = false;
        }
        break;
      case 'house-sale':
        $increment  = 500;
        $division   = 1000;

        if ($options['region_id'] == 77) {
          $link = 'http://www.bn.ru/msk/country/search/?price1=%d&price2=%d&so1='.ParseTools::MIN_S.'&action_id[0]=1&sortordtype=1&sorttype=0';
        } elseif ($options['region_id'] == 52) {
          $link = 'http://www.bn.ru/regions/country/search/?form[minprice]=%d&form[maxprice]=%d&form[mintotal]='.ParseTools::MIN_S.'&form[markettypes][0]=1&form[area]=59&sortordtype=1&sorttype=1';
        } elseif ($options['region_id'] == 39) {
          $link = '';
        }
        break;
      case 'house-rent':
        $increment  = 20000;

        if ($options['region_id'] == 77) {
          $link = 'http://www.bn.ru/msk/country/search/?price1=%d&price2=%d&so1='.ParseTools::MIN_S.'&action_id[0]=2&sortordtype=1&sorttype=0';
        } elseif ($options['region_id'] == 52) {
          $link = 'http://www.bn.ru/regions/country/search/?form[minprice]=%d&form[maxprice]=%d&form[mintotal]='.ParseTools::MIN_S.'&form[markettypes][0]=1&form[area]=59&sortordtype=1&sorttype=1';
        } elseif ($options['region_id'] == 39) {
          $link = false;
        }
        break;
    }

    if (!$link) return false;

    if (!$price1 || !$price2) {
      $price1 = ($options['worker']-1)*$increment;
      $price2 = $price1+($increment-1);

      $minprice = ParseTools::getMinPrice($options['type'], $options['region_id']);
      if ($division) $minprice /= $division;

      if ($price2 < $minprice) {
        return false;
      } elseif ($price1 < $minprice) {
        $price1 = $minprice;
      }
    }

    $link = sprintf($link, $price1, $price2);

    $this->settings = array(
      $options['type']      => array(
        $options['region_id'] => array(
          $options['worker']-1  => array(
            'url'   => $link,
            'data'  => array('region_id' => $options['region_id'], 'user_id' => 5, 'type' => $options['type'])
          )
        )
      )
    );

    return true;
  }

  private $settings = array(
    'commercial-sale' => array(
      0 => array(
        0 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=13&action_id[0]=1&price1=10&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Бизнес-центр')),
          'note'  => 'sale bc in msk & mo'
        ),
        1 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=105&action_id[0]=1&price1=10&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Гостиница, мотель')),
          'note'  => 'sale hotel in msk & mo'
        ),
        2 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=7&action_id[0]=1&price1=10&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Земля')),
          'note'  => 'sale area in msk & mo'
        ),
        3 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=101&action_id[0]=1&price1=10&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Магазин')),
          'note'  => 'sale shop in msk & mo'
        ),
        4 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=6&action_id[0]=1&price1=10&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Отд. стоящее здание')),
          'note'  => 'sale build in msk & mo'
        ),
        5 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=2&action_id[0]=1&price1=10&price2=14000&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Офис')),
          'note'  => 'sale office in msk & mo'
        ),
        6 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=2&action_id[0]=1&price1=14001&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Офис')),
          'note'  => 'sale office in msk & mo'
        ),
        7 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=104&action_id[0]=1&price1=10&price2=20500&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Свободного назначения')),
          'note'  => 'sale wtf in msk & mo'
        ),
        8 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=104&action_id[0]=1&price1=20501&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Свободного назначения')),
          'note'  => 'sale wtf in msk & mo'
        ),
        9 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=103&action_id[0]=1&price1=10&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Ресторан/кафе')),
          'note'  => 'sale food-store in msk & mo'
        ),
        10 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=3&action_id[0]=1&price1=10&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Производ. площади')),
          'note'  => 'sale fabrique in msk & mo'
        ),
        11=> array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=102&action_id[0]=1&price1=10&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Склад')),
          'note'  => 'sale sklad in msk & mo'
        ),
        12 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=108&type_id[1]=4&action_id[0]=1&price1=10&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Торговые площади')),
          'note'  => 'sale sale-area in msk & mo'
        ),
        13 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=106&type_id[1]=5&action_id[0]=1&price1=10&price2=2900&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Другое')),
          'note'  => 'sale other in msk & mo'
        ),
        14 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=106&type_id[1]=5&action_id[0]=1&price1=2901&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Другое')),
          'note'  => 'sale other in msk & mo'
        ),
        15 => array(
          'url'   => 'http://www.bn.ru/regions/commerce/search/?form[minprice]=10&form[markettypes][]=1&form[objects][]=2&form[area]=59',
          'data'  => array('region_id' => 52, 'user_id' => 5, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Офис')),
          'note'  => 'sale office in nn'
        ),
        16 => array(
          'url'   => 'http://www.bn.ru/regions/commerce/search/?form[minprice]=10&form[markettypes][]=1&form[objects][]=3&form[area]=59',
          'data'  => array('region_id' => 52, 'user_id' => 5, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Производ. площади')),
          'note'  => 'sale fabrique in nn'
        ),
        17 => array(
          'url'   => 'http://www.bn.ru/regions/commerce/search/?form[minprice]=10&form[markettypes][]=1&form[objects][]=4&form[area]=59',
          'data'  => array('region_id' => 52, 'user_id' => 5, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Торговые площади')),
          'note'  => 'sale sale-area in nn'
        ),
        18 => array(
          'url'   => 'http://www.bn.ru/regions/commerce/search/?form[minprice]=10&form[markettypes][]=1&form[objects][]=6&form[area]=59',
          'data'  => array('region_id' => 52, 'user_id' => 5, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Отд. стоящее здание')),
          'note'  => 'sale build in nn'
        ),
        19 => array(
          'url'   => 'http://www.bn.ru/regions/commerce/search/?form[minprice]=10&form[markettypes][]=1&form[objects][]=7&form[area]=59',
          'data'  => array('region_id' => 52, 'user_id' => 5, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Земля')),
          'note'  => 'sale area in nn'
        ),
        20 => array(
          'url'   => 'http://www.bn.ru/regions/commerce/search/?form[minprice]=10&form[markettypes][]=1&form[objects][]=5&form[area]=59',
          'data'  => array('region_id' => 52, 'user_id' => 5, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Другое')),
          'note'  => 'sale other in nn'
        ),
      ),
    ),
    'commercial-rent' => array(
      0 => array(
        0 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=13&action_id[0]=2&price1=10&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Бизнес-центр')),
          'note'  => 'rent bc in msk & mo'
        ),
        1 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=105&action_id[0]=2&price1=10&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Гостиница, мотель')),
          'note'  => 'rent hotel in msk & mo'
        ),
        2 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=7&action_id[0]=2&price1=10&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Земля')),
          'note'  => 'rent area in msk & mo'
        ),
        3 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=101&action_id[0]=2&price1=10&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Магазин')),
          'note'  => 'rent shop in msk & mo'
        ),
        4 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=6&action_id[0]=2&price1=10&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Отд. стоящее здание')),
          'note'  => 'rent build in msk & mo'
        ),
        5 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=2&action_id[0]=2&price1=10&price2=10000&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Офис')),
          'note'  => 'rent office in msk & mo'
        ),
        6 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=2&action_id[0]=2&price1=10001&price2=15000&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Офис')),
          'note'  => 'rent office in msk & mo'
        ),
        7 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=2&action_id[0]=2&price1=15001&price2=25000&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Офис')),
          'note'  => 'rent office in msk & mo'
        ),
        8 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=2&action_id[0]=2&price1=25001&price2=50000&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Офис')),
          'note'  => 'rent office in msk & mo'
        ),
        9 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=2&action_id[0]=2&price1=50001&price2=90000&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Офис')),
          'note'  => 'rent office in msk & mo'
        ),
        10 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=2&action_id[0]=2&price1=90001&price2=150000&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Офис')),
          'note'  => 'rent office in msk & mo'
        ),
        11 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=2&action_id[0]=2&price1=150001&price2=250000&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Офис')),
          'note'  => 'rent office in msk & mo'
        ),
        12 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=2&action_id[0]=2&price1=250001&price2=600000&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Офис')),
          'note'  => 'rent office in msk & mo'
        ),
        13 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=104&action_id[0]=2&price1=10&price2=200000&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Свободного назначения')),
          'note'  => 'rent wtf in msk & mo'
        ),
        14 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=104&action_id[0]=2&price1=200001&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Свободного назначения')),
          'note'  => 'rent wtf in msk & mo'
        ),
        15 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=103&action_id[0]=2&price1=10&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Ресторан/кафе')),
          'note'  => 'rent food-store in msk & mo'
        ),
        16 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=3&action_id[0]=2&price1=10&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Производ. площади')),
          'note'  => 'rent fabrique in msk & mo'
        ),
        17 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=102&action_id[0]=2&price1=10&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Склад')),
          'note'  => 'rent sklad in msk & mo'
        ),
        18 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=108&type_id[1]=4&action_id[0]=2&price1=10&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Торговые площади')),
          'note'  => 'rent sale-area in msk & mo'
        ),
        19 => array(
          'url'   => 'http://www.bn.ru/msk/commerce/search/?type_id[0]=106&type_id[1]=5&action_id[0]=2&price1=10&sortordtype=1&sorttype=0',
          'data'  => array('region_id' => 77, 'user_id' => 5, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Другое')),
          'note'  => 'rent other in msk & mo'
        ),
      ),
    ),
  );


  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    if ($options['region_id'] !== 0) {
      if (!$this->generateLink($options)) return;
    }

    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);

    if (!empty($this->settings[$options['type']][$options['region_id']])) {

      if (empty($options['worker'])) {
        ini_set('memory_limit', '512M');
        foreach ($this->settings[$options['type']][$options['region_id']] as $settings) {
          $settings = array_merge(array('limit' => $options['limit']), $settings);
          $log_options = array(
            'resource' => 'BN',
            'type'      => $options['type'],
            'limit'     => $options['limit'],
            'page'      => $settings['url'],
          );
          ParseLogger::initLogger($log_options);

          $fetcher = new Fetcher_Bn($settings, array($this, 'writeProgress'));
          $fetcher->get();

          ParseLogger::writeFinish($fetcher->lots_parsed, $fetcher->lots_fetched);
        }
      }
      elseif (!empty($this->settings[$options['type']][$options['region_id']][$options['worker'] -1])) {
        ini_set('memory_limit', '300M');
        $settings = $this->settings[$options['type']][$options['region_id']][$options['worker'] -1];
        $settings = array_merge(array('limit' => $options['limit']), $settings);
        $log_options = array(
          'resource' => 'BN',
          'type'      => $options['type'],
          'limit'     => $options['limit'],
          'page'      => $settings['url'],
        );
        ParseLogger::initLogger($log_options);

        $fetcher = new Fetcher_Bn($settings);
        $fetcher->get();

        ParseLogger::writeFinish($fetcher->lots_parsed, $fetcher->lots_fetched);

        unset($fetcher);
      }
    }
  }
}
