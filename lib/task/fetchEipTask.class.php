<?php
/**
 * Fetch lots from eip.ru
 *
 * @package    domus
 * @subpackage task
 */
class fetchEipTask extends sfBaseTask
{
  protected
    $config = null;

  private
    $price1 = null,
    $price2 = null,

    $settings = array(
      'commercial-sale' => array(
        0 => array(
          'url'   => 'http://www.eip.ru/view/commerce/?city=74&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=2&price2=4000000&p=1',
          'data'  => array('region_id' => 77, 'user_id' => 12, 'type' => 'commercial-sale')
        ),
        1 => array(
          'url'   => 'http://www.eip.ru/view/commerce/?city=74&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=2&price1=4000001&price2=50000000&p=1',
          'data'  => array('region_id' => 77, 'user_id' => 12, 'type' => 'commercial-sale')
        ),
        2 => array(
          'url'   => 'http://www.eip.ru/view/commerce/?city=74&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=2&price1=50000001&p=1',
          'data'  => array('region_id' => 77, 'user_id' => 12, 'type' => 'commercial-sale')
        ),
        3 => array(
          'url'   => 'http://www.eip.ru/view/commerce/?city=118&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=2&price2=3000000&p=1',
          'data'  => array('region_id' => 78, 'user_id' => 12, 'type' => 'commercial-sale')
        ),
        4 => array(
          'url'   => 'http://www.eip.ru/view/commerce/?city=118&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=2&price1=3000001&price2=7000000&p=1',
          'data'  => array('region_id' => 78, 'user_id' => 12, 'type' => 'commercial-sale')
        ),
        5 => array(
          'url'   => 'http://www.eip.ru/view/commerce/?city=118&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=2&price1=7000001&price2=14000000&p=1',
          'data'  => array('region_id' => 78, 'user_id' => 12, 'type' => 'commercial-sale')
        ),
        6 => array(
          'url'   => 'http://www.eip.ru/view/commerce/?city=118&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=2&price1=14000001&price2=40000000&p=1',
          'data'  => array('region_id' => 78, 'user_id' => 12, 'type' => 'commercial-sale')
        ),
        7 => array(
          'url'   => 'http://www.eip.ru/view/commerce/?city=118&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=2&price1=40000001&p=1',
          'data'  => array('region_id' => 78, 'user_id' => 12, 'type' => 'commercial-sale')
        ),
        8 => array(
          'url'   => 'http://www.eip.ru/view/commerce/?city=84&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=2&p=1',
          'data'  => array('region_id' => 52, 'user_id' => 12, 'type' => 'commercial-sale')
        ),
        9 => array(
          'url'   => 'http://www.eip.ru/view/commerce/?city=59&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=2&price2=18000000&p=1',
          'data'  => array('region_id' => 23, 'user_id' => 12, 'type' => 'commercial-sale')
        ),
        10 => array(
          'url'   => 'http://www.eip.ru/view/commerce/?city=59&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=2&price1=18000001&p=1',
          'data'  => array('region_id' => 23, 'user_id' => 12, 'type' => 'commercial-sale')
        ),
        11 => array(
          'url'   => 'http://www.eip.ru/view/commerce/?city=19&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=2&p=1',
          'data'  => array('region_id' => 34, 'user_id' => 12, 'type' => 'commercial-sale')
        ),
      ),
      'commercial-rent' => array(
        0 => array(
          'url' => 'http://www.eip.ru/view/commerce/?city=74&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=5&price2=200000&p=1',
          'data' => array('region_id' => 77, 'user_id' => 12, 'type' => 'commercial-rent')
        ),
        1 => array(
          'url' => 'http://www.eip.ru/view/commerce/?city=74&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=5&price1=200001&price2=450000&p=1',
          'data' => array('region_id' => 77, 'user_id' => 12, 'type' => 'commercial-rent')
        ),
        2 => array(
          'url' => 'http://www.eip.ru/view/commerce/?city=74&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=5&price1=450001&price2=800000&p=1',
          'data' => array('region_id' => 77, 'user_id' => 12, 'type' => 'commercial-rent')
        ),
        3 => array(
          'url' => 'http://www.eip.ru/view/commerce/?city=74&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=5&price1=800001&price2=1300000&p=1',
          'data' => array('region_id' => 77, 'user_id' => 12, 'type' => 'commercial-rent')
        ),
        4 => array(
          'url' => 'http://www.eip.ru/view/commerce/?city=74&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=5&price1=1300001&price2=2000000&p=1',
          'data' => array('region_id' => 77, 'user_id' => 12, 'type' => 'commercial-rent')
        ),
        5 => array(
          'url' => 'http://www.eip.ru/view/commerce/?city=74&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=5&price1=2000001&price2=3000000&p=1',
          'data' => array('region_id' => 77, 'user_id' => 12, 'type' => 'commercial-rent')
        ),
        6 => array(
          'url' => 'http://www.eip.ru/view/commerce/?city=74&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=5&price1=3000001&price2=5000000&p=1',
          'data' => array('region_id' => 77, 'user_id' => 12, 'type' => 'commercial-rent')
        ),
        7 => array(
          'url' => 'http://www.eip.ru/view/commerce/?city=74&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=5&price1=5000001&price2=10000000&p=1',
          'data' => array('region_id' => 77, 'user_id' => 12, 'type' => 'commercial-rent')
        ),
        8 => array(
          'url' => 'http://www.eip.ru/view/commerce/?city=74&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=5&price1=10000001&p=1',
          'data' => array('region_id' => 77, 'user_id' => 12, 'type' => 'commercial-rent')
        ),
        9 => array(
          'url' => 'http://www.eip.ru/view/commerce/?city=118&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=5&price2=15000&p=1',
          'data' => array('region_id' => 78, 'user_id' => 12, 'type' => 'commercial-rent')
        ),
        10 => array(
          'url' => 'http://www.eip.ru/view/commerce/?city=118&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=5&price1=15001&price2=45000&p=1',
          'data' => array('region_id' => 78, 'user_id' => 12, 'type' => 'commercial-rent')
        ),
        11 => array(
          'url' => 'http://www.eip.ru/view/commerce/?city=118&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=5&price1=45001&price2=70000&p=1',
          'data' => array('region_id' => 78, 'user_id' => 12, 'type' => 'commercial-rent')
        ),
        12 => array(
          'url' => 'http://www.eip.ru/view/commerce/?city=118&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=5&price1=70001&price2=100000&p=1',
          'data' => array('region_id' => 78, 'user_id' => 12, 'type' => 'commercial-rent')
        ),
        13 => array(
          'url' => 'http://www.eip.ru/view/commerce/?city=118&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=5&price1=100001&price2=150000&p=1',
          'data' => array('region_id' => 78, 'user_id' => 12, 'type' => 'commercial-rent')
        ),
        14 => array(
          'url' => 'http://www.eip.ru/view/commerce/?city=118&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=5&price1=150001&price2=250000&p=1',
          'data' => array('region_id' => 78, 'user_id' => 12, 'type' => 'commercial-rent')
        ),
        15 => array(
          'url' => 'http://www.eip.ru/view/commerce/?city=118&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=5&price1=250001&price2=600000&p=1',
          'data' => array('region_id' => 78, 'user_id' => 12, 'type' => 'commercial-rent')
        ),
        16 => array(
          'url' => 'http://www.eip.ru/view/commerce/?city=118&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=5&price1=600001&p=1',
          'data' => array('region_id' => 78, 'user_id' => 12, 'type' => 'commercial-rent')
        ),
        17 => array(
          'url' => 'http://www.eip.ru/view/commerce/?city=84&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=5&p=1',
          'data' => array('region_id' => 52, 'user_id' => 12, 'type' => 'commercial-rent')
        ),
        18 => array(
          'url' => 'http://www.eip.ru/view/commerce/?city=59&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=5&p=1',
          'data' => array('region_id' => 23, 'user_id' => 12, 'type' => 'commercial-rent')
        ),
        19 => array(
          'url' => 'http://www.eip.ru/view/commerce/?city=19&what=%ED%E4%E2,%EE%F4%F1,%F1%EA%EB,%EE%E1%F1,%EE%F1%E7,%EA%E7%F3,%EA%ED.,%E3%E0%F0&oper=5&p=1',
          'data' => array('region_id' => 34, 'user_id' => 12, 'type' => 'commercial-rent')
        ),
      ),
    );

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->namespace = 'fetch';
    $this->name = 'eip';
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
      77 => 74,   78 => 118,
      52 => 84,   23 => 59,
      34 => 19,
    );

    switch ($options['type']) {
      case 'apartament-sale':
        $increment  = 200000;
        $max_price  = 20000000;

        $offer_type = 'living';
        $oper = 2;
        $params = '&what=%EA%E2.,%EA%F1%E4';
        break;

      case 'apartament-rent':
        $increment  = 5000;
        $max_price  = 100000;

        $offer_type = 'living';
        $oper = 5;
        $params = '';
        break;

      case 'house-sale':
        $increment  = 500000;
        $max_price  = 50000000;

        $offer_type = 'country';
        $oper = 2;
        $params = '';
        break;

      case 'house-rent':
        $increment  = 20000;
        $max_price  = 200000;

        $offer_type = 'country';
        $oper = 5;
        $params = '';
        break;
      
      default:
        return $this->getLinkFromSettings($options, $worker);
    }

    if (!isset($regions[$options['region_id']], $increment, $max_price, $offer_type, $oper, $params)) return false;

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

    $link = sprintf('http://www.eip.ru/view/%s/?city=%d%s&oper=%d&area1=%d&price1=%d&price2=%d&p=1',
                    $offer_type, $regions[$options['region_id']], $params, $oper, ParseTools::MIN_S, $price1, $price2);
    
    $settings = array(
      'url'   => $link,
      'data'  => array('region_id' => $options['region_id'], 'user_id' => 12, 'type' => $options['type']),
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
      'resource'  => 'Eip',
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

    $fetcher = new Fetcher_Eip($settings);
    $fetcher->get();

    ParseLogger::writeFinish($fetcher->lots_parsed, $fetcher->lots_fetched);
  }
}
