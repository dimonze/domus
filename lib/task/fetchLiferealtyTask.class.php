<?php
/**
 * Fetch lots from life-realty.ru
 *
 * @package    domus
 * @subpackage task
 */
class fetchLiferealtyTask extends sfBaseTask
{
  protected
    $config = null;

  private
    $price1 = null,
    $price2 = null,

    $settings = array(
      'commercial-sale' => array(
        0 => array(
          'url'   => 'http://rostov.life-realty.ru/commerce/?view=simple&commerceRubric=1&priceFrom=5&priceTo=3000&areaFrom=8&page=1',
          'data'  => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-sale')
        ),
        1 => array(
          'url'   => 'http://rostov.life-realty.ru/commerce/?view=simple&commerceRubric=1&priceFrom=3001&priceTo=5000&areaFrom=8&page=1',
          'data'  => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-sale')
        ),
        2 => array(
          'url'   => 'http://rostov.life-realty.ru/commerce/?view=simple&commerceRubric=1&priceFrom=5001&priceTo=10000&areaFrom=8&page=1',
          'data'  => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-sale')
        ),
        3 => array(
          'url'   => 'http://rostov.life-realty.ru/commerce/?view=simple&commerceRubric=1&priceFrom=10001&areaFrom=8&page=1',
          'data'  => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-sale')
        ),
        4 => array(
          'url'   => 'http://rostov.life-realty.ru/commerce/?view=simple&commerceRubric=2&priceFrom=5&priceTo=3000&areaFrom=8&page=1',
          'data'  => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-sale')
        ),
        5 => array(
          'url'   => 'http://rostov.life-realty.ru/commerce/?view=simple&commerceRubric=2&priceFrom=3001&priceTo=4500&areaFrom=8&page=1',
          'data'  => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-sale')
        ),
        6 => array(
          'url'   => 'http://rostov.life-realty.ru/commerce/?view=simple&commerceRubric=2&priceFrom=4501&priceTo=6000&areaFrom=8&page=1',
          'data'  => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-sale')
        ),
        7 => array(
          'url'   => 'http://rostov.life-realty.ru/commerce/?view=simple&commerceRubric=2&priceFrom=6001&priceTo=10000&areaFrom=8&page=1',
          'data'  => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-sale')
        ),
        8 => array(
          'url'   => 'http://rostov.life-realty.ru/commerce/?view=simple&commerceRubric=2&priceFrom=10001&priceTo=25000&areaFrom=8&page=1',
          'data'  => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-sale')
        ),
        9 => array(
          'url'   => 'http://rostov.life-realty.ru/commerce/?view=simple&commerceRubric=2&priceFrom=25001&areaFrom=8&page=1',
          'data'  => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-sale')
        ),
        10 => array(
          'url'   => 'http://rostov.life-realty.ru/commerce/?view=simple&commerceRubric=3&priceFrom=5&areaFrom=8&page=1',
          'data'  => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-sale')
        ),
        11 => array(
          'url'   => 'http://rostov.life-realty.ru/commerce/?view=simple&commerceRubric=4&priceFrom=5&areaFrom=8&page=1',
          'data'  => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-sale')
        ),
        12 => array(
          'url'   => 'http://rostov.life-realty.ru/commerce/?view=simple&commerceRubric=5&priceFrom=5&priceTo=10000&page=1',
          'data'  => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-sale')
        ),
        13 => array(
          'url'   => 'http://rostov.life-realty.ru/commerce/?view=simple&commerceRubric=5&priceFrom=10001&page=1',
          'data'  => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-sale')
        ),
        14 => array(
          'url'   => 'http://krasnodar.life-realty.ru/commerce/?view=simple&commerceRubric=1&priceFrom=5&priceTo=10000&areaFrom=8&page=1',
          'data'  => array('region_id' => 23, 'user_id' => 6409, 'type' => 'commercial-sale')
        ),
        15 => array(
          'url'   => 'http://krasnodar.life-realty.ru/commerce/?view=simple&commerceRubric=1&priceFrom=10001&areaFrom=8&page=1',
          'data'  => array('region_id' => 23, 'user_id' => 6409, 'type' => 'commercial-sale')
        ),
        16 => array(
          'url'   => 'http://krasnodar.life-realty.ru/commerce/?view=simple&commerceRubric=2&priceFrom=5&priceTo=10000&areaFrom=8&page=1',
          'data'  => array('region_id' => 23, 'user_id' => 6409, 'type' => 'commercial-sale')
        ),
        17 => array(
          'url'   => 'http://krasnodar.life-realty.ru/commerce/?view=simple&commerceRubric=2&priceFrom=10001&areaFrom=8&page=1',
          'data'  => array('region_id' => 23, 'user_id' => 6409, 'type' => 'commercial-sale')
        ),
        18 => array(
          'url'   => 'http://krasnodar.life-realty.ru/commerce/?view=simple&commerceRubric=3&priceFrom=5&areaFrom=8&page=1',
          'data'  => array('region_id' => 23, 'user_id' => 6409, 'type' => 'commercial-sale')
        ),
        19 => array(
          'url'   => 'http://krasnodar.life-realty.ru/commerce/?view=simple&commerceRubric=4&priceFrom=5&areaFrom=8&page=1',
          'data'  => array('region_id' => 23, 'user_id' => 6409, 'type' => 'commercial-sale')
        ),
        20 => array(
          'url'   => 'http://krasnodar.life-realty.ru/commerce/?view=simple&commerceRubric=5&priceFrom=5&page=1',
          'data'  => array('region_id' => 23, 'user_id' => 6409, 'type' => 'commercial-sale')
        ),
      ),
      'commercial-rent' => array(
        0 => array(
          'url' => 'http://rostov.life-realty.ru/commerce-rent/?view=simple&commerceRubric=1&priceFrom=5&priceTo=10&areaFrom=8&page=1',
          'data' => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-rent')
        ),
        1 => array(
          'url' => 'http://rostov.life-realty.ru/commerce-rent/?view=simple&commerceRubric=1&priceFrom=11&priceTo=15&areaFrom=8&page=1',
          'data' => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-rent')
        ),
        2 => array(
          'url' => 'http://rostov.life-realty.ru/commerce-rent/?view=simple&commerceRubric=1&priceFrom=16&priceTo=25&areaFrom=8&page=1',
          'data' => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-rent')
        ),
        3 => array(
          'url' => 'http://rostov.life-realty.ru/commerce-rent/?view=simple&commerceRubric=1&priceFrom=26&priceTo=40&areaFrom=8&page=1',
          'data' => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-rent')
        ),
        4 => array(
          'url' => 'http://rostov.life-realty.ru/commerce-rent/?view=simple&commerceRubric=1&priceFrom=41&priceTo=60&areaFrom=8&page=1',
          'data' => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-rent')
        ),
        5 => array(
          'url' => 'http://rostov.life-realty.ru/commerce-rent/?view=simple&commerceRubric=1&priceFrom=61&priceTo=170&areaFrom=8&page=1',
          'data' => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-rent')
        ),
        6 => array(
          'url' => 'http://rostov.life-realty.ru/commerce-rent/?view=simple&commerceRubric=1&priceFrom=171&areaFrom=8&page=1',
          'data' => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-rent')
        ),
        7 => array(
          'url' => 'http://rostov.life-realty.ru/commerce-rent/?view=simple&commerceRubric=2&priceFrom=5&priceTo=25&areaFrom=8&page=1',
          'data' => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-rent')
        ),
        8 => array(
          'url' => 'http://rostov.life-realty.ru/commerce-rent/?view=simple&commerceRubric=2&priceFrom=26&priceTo=40&areaFrom=8&page=1',
          'data' => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-rent')
        ),
        9 => array(
          'url' => 'http://rostov.life-realty.ru/commerce-rent/?view=simple&commerceRubric=2&priceFrom=41&priceTo=60&areaFrom=8&page=1',
          'data' => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-rent')
        ),
        10 => array(
          'url' => 'http://rostov.life-realty.ru/commerce-rent/?view=simple&commerceRubric=2&priceFrom=61&priceTo=100&areaFrom=8&page=1',
          'data' => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-rent')
        ),
        11 => array(
          'url' => 'http://rostov.life-realty.ru/commerce-rent/?view=simple&commerceRubric=2&priceFrom=101&areaFrom=8&page=1',
          'data' => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-rent')
        ),
        12 => array(
          'url' => 'http://rostov.life-realty.ru/commerce-rent/?view=simple&commerceRubric=3&priceFrom=5&areaFrom=50&page=1',
          'data' => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-rent')
        ),
        13 => array(
          'url' => 'http://rostov.life-realty.ru/commerce-rent/?view=simple&commerceRubric=3&priceFrom=51&page=1',
          'data' => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-rent')
        ),
        14 => array(
          'url' => 'http://rostov.life-realty.ru/commerce-rent/?view=simple&commerceRubric=4&priceFrom=5&page=1',
          'data' => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-rent')
        ),
        15 => array(
          'url' => 'http://rostov.life-realty.ru/commerce-rent/?view=simple&commerceRubric=5&priceFrom=5&page=1',
          'data' => array('region_id' => 61, 'user_id' => 6409, 'type' => 'commercial-rent')
        ),
        16 => array(
          'url' => 'http://krasnodar.life-realty.ru/commerce-rent/?view=simple&commerceRubric=1&priceFrom=5&areaFrom=8&page=1',
          'data' => array('region_id' => 23, 'user_id' => 6409, 'type' => 'commercial-rent')
        ),
        17 => array(
          'url' => 'http://krasnodar.life-realty.ru/commerce-rent/?view=simple&commerceRubric=2&priceFrom=5&areaFrom=8&page=1',
          'data' => array('region_id' => 23, 'user_id' => 6409, 'type' => 'commercial-rent')
        ),
        18 => array(
          'url' => 'http://krasnodar.life-realty.ru/commerce-rent/?view=simple&commerceRubric=3&priceFrom=5&areaFrom=8&page=1',
          'data' => array('region_id' => 23, 'user_id' => 6409, 'type' => 'commercial-rent')
        ),
        19 => array(
          'url' => 'http://krasnodar.life-realty.ru/commerce-rent/?view=simple&commerceRubric=4&priceFrom=5&areaFrom=8&page=1',
          'data' => array('region_id' => 23, 'user_id' => 6409, 'type' => 'commercial-rent')
        ),
        20 => array(
          'url' => 'http://krasnodar.life-realty.ru/commerce-rent/?view=simple&commerceRubric=5&priceFrom=5&page=1',
          'data' => array('region_id' => 23, 'user_id' => 6409, 'type' => 'commercial-rent')
        ),
      ),
    );

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->namespace = 'fetch';
    $this->name = 'liferealty';
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
      61 => 'rostov',
      23 => 'krasnodar',
    );

    switch ($options['type']) {
      case 'apartament-sale':
        $increment  = 100000;
        $max_price  = 20000000;

        $offer_type = 'sale';
        $params = '&townType[]=1&townType[]=2&townType[]=3&townType[]=4&townType[]=5&townType[]=6';
        break;

      case 'apartament-rent':
        $increment  = 5000;
        $max_price  = 100000;

        $offer_type = 'rent';
        $params = '&townType[]=1&townType[]=2&townType[]=3&townType[]=4&townType[]=5&townType[]=6';
        break;

      case 'house-sale':
        $increment  = 500000;
        $max_price  = 50000000;

        $offer_type = 'country';
        $params = '&countryType[]=1&countryType[]=2&countryType[]=4&countryType[]=4';
        break;
      
      case 'house-rent':
        $increment  = 20000;
        $max_price  = 200000;

        $offer_type = 'country-rent';
        $params = '&countryType[]=1&countryType[]=2&countryType[]=4&countryType[]=4';
        break;
      
      default:
        return $this->getLinkFromSettings($options, $worker);
    }

    if (!isset($regions[$options['region_id']], $increment, $max_price, $offer_type, $params)) return false;

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

    $link = sprintf('http://%s.life-realty.ru/%s/?view=simple%s&priceFrom=%d&priceTo=%d&areaTotalFrom=%d&page=1',
                    $regions[$options['region_id']], $offer_type, $params, $price1/1000, $price2/1000, ParseTools::MIN_S);
    $settings = array(
      'url'   => $link,
      'data'  => array('region_id' => $options['region_id'], 'user_id' => 6409, 'type' => $options['type']),
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
      'resource'  => 'Liferealty',
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

    $fetcher = new Fetcher_Liferealty($settings);
    $fetcher->get();

    ParseLogger::writeFinish($fetcher->lots_parsed, $fetcher->lots_fetched);
    
    unset($fetcher);
  }
}
