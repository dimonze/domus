<?php
/**
 * Fetch lots from life-realty.ru
 *
 * @package    domus
 * @subpackage task
 */
class fetch34metraTask extends sfBaseTask
{
  protected
    $config = null;

  private
    $settings = array(
      'apartament-sale' => array(
        0 => array(
          'url'   => 'http://34metra.ru/realty/sell/residential/secondary/1.php?order=DateUpdate&dir=desc&price_unit=1&area_unit=1&expand=0&PriceUnit=1&Price_min=120&Price_max=2199',
          'data'  => array('region_id' => 34, 'user_id' => 10297, 'type' => 'apartament-sale'),
        ),
        1 => array(
          'url'   => 'http://34metra.ru/realty/sell/residential/secondary/1.php?order=DateUpdate&dir=desc&price_unit=1&area_unit=1&expand=0&PriceUnit=1&Price_min=2200',
          'data'  => array('region_id' => 34, 'user_id' => 10297, 'type' => 'apartament-sale'),
        ),
        2 => array(
          'url'   => 'http://34metra.ru/realty/sell/residential/new/1.php?order=DateUpdate&dir=desc&price_unit=1&area_unit=1&expand=0&PriceUnit=1&Price_min=120&Price_max=2999',
          'data'  => array('region_id' => 34, 'user_id' => 10297, 'type' => 'apartament-sale'),
        ),
        3 => array(
          'url'   => 'http://34metra.ru/realty/sell/residential/new/1.php?order=DateUpdate&dir=desc&price_unit=1&area_unit=1&expand=0&PriceUnit=1&Price_min=3000&Price_max=4999',
          'data'  => array('region_id' => 34, 'user_id' => 10297, 'type' => 'apartament-sale'),
        ),
        4 => array(
          'url'   => 'http://34metra.ru/realty/sell/residential/new/1.php?order=DateUpdate&dir=desc&price_unit=1&area_unit=1&expand=0&PriceUnit=1&Price_min=5000',
          'data'  => array('region_id' => 34, 'user_id' => 10297, 'type' => 'apartament-sale'),
        ),
      ),
      'apartament-rent' => array(
        0 => array(
          'url'   => 'http://34metra.ru/realty/lease/residential/secondary/1.php?order=DateUpdate&dir=desc&price_unit=1&area_unit=1&expand=0&PriceUnit=1&Price_min=5',
          'data'  => array('region_id' => 34, 'user_id' => 10297, 'type' => 'apartament-rent'),
        ),
        1 => array(
          'url'   => 'http://34metra.ru/realty/lease/residential/new/1.php?order=DateUpdate&dir=desc&price_unit=1&area_unit=1&expand=0&PriceUnit=1&Price_min=5',
          'data'  => array('region_id' => 34, 'user_id' => 10297, 'type' => 'apartament-rent'),
        ),
      ),
      'house-sale' => array(
        0 => array(
          'url'   => 'http://34metra.ru/realty/sell/residential/houses/1.php?order=DateUpdate&dir=desc&price_unit=1&area_unit=2&expand=0&PriceUnit=1&Price_min=15',
          'data'  => array('region_id' => 34, 'user_id' => 10297, 'type' => 'house-sale'),
        ),
        1 => array(
          'url'   => 'http://34metra.ru/realty/sell/residential/gardens/1.php?order=DateUpdate&dir=desc&price_unit=1&area_unit=2&expand=0&PriceUnit=1&Price_min=15',
          'data'  => array('region_id' => 34, 'user_id' => 10297, 'type' => 'house-sale'),
        ),
        /*2 => array(
          'url'   => 'http://34metra.ru/realty/sell/land/housing/1.php?order=DateUpdate&dir=desc&price_unit=1&area_unit=2&expand=0&PriceUnit=1',
          'data'  => array('region_id' => 34, 'user_id' => 10297, 'type' => 'house-sale'),
        ),*/
      ),
      'house-rent' => array(
        0 => array(
          'url'   => 'http://34metra.ru/realty/lease/residential/houses/1.php?order=DateUpdate&dir=desc&price_unit=1&area_unit=2&expand=0&PriceUnit=1&Price_min=5',
          'data'  => array('region_id' => 34, 'user_id' => 10297, 'type' => 'house-rent'),
        ),
        1 => array(
          'url'   => 'http://34metra.ru/realty/lease/residential/gardens/1.php?order=DateUpdate&dir=desc&price_unit=1&area_unit=2&expand=0&PriceUnit=1&Price_min=5',
          'data'  => array('region_id' => 34, 'user_id' => 10297, 'type' => 'house-rent'),
        ),
      ),
      'commercial-sale' => array(
        0 => array(
          'url'   => 'http://34metra.ru/realty/sell/commerce/office/1.php?order=DateUpdate&dir=desc&price_unit=1&area_unit=1&expand=0&PriceUnit=1&Price_min=160',
          'data'  => array('region_id' => 34, 'user_id' => 10297, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Офис'))
        ),
        1 => array(
          'url'   => 'http://34metra.ru/realty/sell/commerce/trade/1.php?order=DateUpdate&dir=desc&price_unit=1&area_unit=1&expand=0&PriceUnit=1&Price_min=160',
          'data'  => array('region_id' => 34, 'user_id' => 10297, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Торговые площади'))
        ),
        2 => array(
          'url'   => 'http://34metra.ru/realty/sell/commerce/production/1.php?order=DateUpdate&dir=desc&price_unit=1&area_unit=1&expand=0&PriceUnit=1&Price_min=160',
          'data'  => array('region_id' => 34, 'user_id' => 10297, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Производ. площади'))
        ),
        3 => array(
          'url'   => 'http://34metra.ru/realty/sell/commerce/warehouse/1.php?order=DateUpdate&dir=desc&price_unit=1&area_unit=1&expand=0&PriceUnit=1&Price_min=160',
          'data'  => array('region_id' => 34, 'user_id' => 10297, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Склад'))
        ),
        4 => array(
          'url'   => 'http://34metra.ru/realty/sell/commerce/other/1.php?order=DateUpdate&dir=desc&price_unit=1&area_unit=1&expand=0&PriceUnit=1&Price_min=160',
          'data'  => array('region_id' => 34, 'user_id' => 10297, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Свободного назначения'))
        ),
        5 => array(
          'url'   => 'http://34metra.ru/realty/sell/land/commercial/1.php?order=DateUpdate&dir=desc&price_unit=1&area_unit=2&expand=0&PriceUnit=1',
          'data'  => array('region_id' => 34, 'user_id' => 10297, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Земля'))
        ),
      ),
      'commercial-rent' => array(
        0 => array(
          'url' => 'http://34metra.ru/realty/lease/commerce/office/1.php?order=DateUpdate&dir=desc&price_unit=2&area_unit=1&expand=0&PriceUnit=1&Price_min=16',
          'data' => array('region_id' => 34, 'user_id' => 10297, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Офис'))
        ),
        1 => array(
          'url' => 'http://34metra.ru/realty/lease/commerce/trade/1.php?order=DateUpdate&dir=desc&price_unit=2&area_unit=1&expand=0&PriceUnit=1&Price_min=16',
          'data' => array('region_id' => 34, 'user_id' => 10297, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Торговые площади'))
        ),
        2 => array(
          'url' => 'http://34metra.ru/realty/lease/commerce/production/1.php?order=DateUpdate&dir=desc&price_unit=2&area_unit=1&expand=0&PriceUnit=1&Price_min=16',
          'data' => array('region_id' => 34, 'user_id' => 10297, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Производ. площади'))
        ),
        3 => array(
          'url' => 'http://34metra.ru/realty/lease/commerce/warehouse/1.php?order=DateUpdate&dir=desc&price_unit=2&area_unit=1&expand=0&PriceUnit=1&Price_min=16',
          'data' => array('region_id' => 34, 'user_id' => 10297, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Склад'))
        ),
        4 => array(
          'url' => 'http://34metra.ru/realty/lease/commerce/other/1.php?order=DateUpdate&dir=desc&price_unit=2&area_unit=1&expand=0&PriceUnit=1&Price_min=16',
          'data' => array('region_id' => 34, 'user_id' => 10297, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Свободного назначения'))
        ),
        5 => array(
          'url' => 'http://34metra.ru/realty/lease/land/commercial/1.php?order=DateUpdate&dir=desc&price_unit=1&area_unit=2&expand=0&PriceUnit=1',
          'data' => array('region_id' => 34, 'user_id' => 10297, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Земля'))
        ),
      ),
    );

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->namespace = 'fetch';
    $this->name = '34metra';
    $this->briefDescription = null;
    $this->detailedDescription = null;

    $this->addOption('type', null, sfCommandOption::PARAMETER_REQUIRED, 'like apartament-sale', 'apartament-sale');
    $this->addOption('worker', null, sfCommandOption::PARAMETER_REQUIRED, 'worker #', null);
    $this->addOption('limit', null, sfCommandOption::PARAMETER_OPTIONAL, 'limit lots count', 150);
    $this->addOption('region_id', null, sfCommandOption::PARAMETER_OPTIONAL, 'region number or 0', 0);
  }

  protected function generateLink($options, $worker)
  {
    if ($options['region_id'] != 34) return 8;
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
      'resource'  => '34metra',
      'type'      => $options['type'],
      'limit'     => $options['limit'],
    );

    if ($options['region_id'] !== 0) {
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

    $fetcher = new Fetcher_34metra($settings);
    $fetcher->get();

    ParseLogger::writeFinish($fetcher->lots_parsed, $fetcher->lots_fetched);
    
    unset($fetcher);
  }
}
