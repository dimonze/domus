<?php
/**
 * Fetch lots from life-realty.ru
 *
 * @package    domus
 * @subpackage task
 */
class fetchVoloradoTask extends sfBaseTask
{
  protected
    $config = null;

  private
    $price1 = null,
    $price2 = null,

    $settings = array(
      'apartament-sale' => array(
        0 => array(
          'url'   => 'http://volorado.ru/nedvizhimost-komnata-v-volgograde.html?wanted=2&city=1&addcost=%D1%80%D1%83%D0%B1&district=0&sortby=datebk&task=komnata&lot=&table=komnata&&dob=&page=1',
          'data'  => array('region_id' => 34, 'user_id' => 10296, 'type' => 'apartament-sale', 'params' => array('Тип предложения' => 'комната')),
        ),
      ),
      'apartament-rent' => array(
        0 => array(
          'url'   => 'http://volorado.ru/nedvizhimost-kvartira-v-volgograde.html?wanted=4&city=1&addcost=%D1%80%D1%83%D0%B1&district=0&sortby=datebk&task=kvartira&lot=&form=kvartira&dob=&page=1',
          'data'  => array('region_id' => 34, 'user_id' => 10296, 'type' => 'apartament-rent'),
        ),
        1 => array(
          'url'   => 'http://volorado.ru/nedvizhimost-komnata-v-volgograde.html?wanted=4&city=1&addcost=%D1%80%D1%83%D0%B1&district=0&sortby=datebk&task=komnata&lot=&table=komnata&&dob=&page=1',
          'data'  => array('region_id' => 34, 'user_id' => 10296, 'type' => 'apartament-rent', 'params' => array('Тип предложения' => 'комната')),
        ),
      ),
      'house-sale' => array(
        0 => array(
          'url'   => 'http://volorado.ru/nedvizhimost-dom-cottedg-v-volgograde.html?wanted=2&city=1&addcost=%F0%F3%E1&district=0&sortby=datebk&task=doma&lot=&table=doma&dob=&page=1',
          'data'  => array('region_id' => 34, 'user_id' => 10296, 'type' => 'house-sale'),
        ),
      ),
      'house-rent' => array(
        0 => array(
          'url'   => 'http://volorado.ru/nedvizhimost-dom-cottedg-v-volgograde.html?wanted=4&city=1&addcost=%F0%F3%E1&district=0&sortby=datebk&task=doma&lot=&table=doma&dob=&page=1',
          'data'  => array('region_id' => 34, 'user_id' => 10296, 'type' => 'house-sale'),
        ),
      ),
      'commercial-sale' => array(
        0 => array(
          'url'   => 'http://volorado.ru/kommercheskaya-nedvizhimost-volgograd.html?wanted=2&type=0&city=1&addcost=%F0%F3%E1&district=0&sortby=datebk&task=kommercheskaya&lot=&table=kommercheskaya&dob=&page=1',
          'data'  => array('region_id' => 34, 'user_id' => 10296, 'type' => 'commercial-sale')
        ),
      ),
      'commercial-rent' => array(
        0 => array(
          'url' => 'http://volorado.ru/kommercheskaya-nedvizhimost-volgograd.html?wanted=4&type=0&city=1&addcost=%F0%F3%E1&district=0&sortby=datebk&task=kommercheskaya&lot=&table=kommercheskaya&dob=&page=1',
          'data' => array('region_id' => 34, 'user_id' => 10296, 'type' => 'commercial-rent')
        ),
      ),
    );

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->namespace = 'fetch';
    $this->name = 'volorado';
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
    if ($options['region_id'] != 34) return 8;

    switch ($options['type']) {
      case 'apartament-sale':
        $increment  = 250000;
        $max_price  = 20000000;

        $offer_type = 'nedvizhimost-kvartira-v-volgograde';
        $oper = 2;
        $params = '&task=kvartira&lot=&form=kvartira&dob=';
        break;

      default:
        return $this->getLinkFromSettings($options, $worker);
    }

    if (!isset($increment, $max_price, $offer_type, $oper, $params)) return false;

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

    $link = sprintf('http://volorado.ru/%s.html?wanted=%d&city=1&from=%d&to=%d&addcost=%s&district=0&sortby=datebk%s&page=1',
                    $offer_type, $oper, $price1/1000, $price2/1000, urlencode('руб'), $params);

    $settings = array(
      'url'   => $link,
      'data'  => array('region_id' => $options['region_id'], 'user_id' => 10296, 'type' => $options['type']),
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
      'resource'  => 'Volorado',
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

    $fetcher = new Fetcher_Volorado($settings);
    $fetcher->get();

    ParseLogger::writeFinish($fetcher->lots_parsed, $fetcher->lots_fetched);
    
    unset($fetcher);
  }
}
