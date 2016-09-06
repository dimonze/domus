<?php
/**
 * Fetch lots from eip.ru
 *
 * @package    domus
 * @subpackage task
 */
class fetchBsnTask extends sfBaseTask
{
  protected
    $config = null;

  private
    $price1 = null,
    $price2 = null,

    $settings = array(
      'apartament-rent' => array(
        0 => array(
          'url'   => 'http://www.bsn.ru/estate/live/rent/search/results/page1/?b_fsqear=8&from[]=F&indagency_live[]=a&indsubw[]=a&indblock[]=18&indblock[]=19&indblock[]=20&indblock[]=21&indblock[]=22&indblock[]=23&indblock[]=24&indblock[]=25&indblock[]=26&indblock[]=27&indblock[]=28&indblock[]=29&indblock[]=30&indblock[]=31&indblock[]=32&indblock[]=33&indblock[]=34&indblock[]=35&indblock[]=36&indblock[]=37&indblock[]=38&indblock[]=39&indblock[]=40&indblock[]=41&indblock[]=42&indblock[]=43&indblock[]=44&indblock[]=45&indblock[]=46&indblock[]=47&indblock[]=48&indblock[]=49&indblock[]=50&indblock[]=51&indblock[]=52&indblock[]=53&indblock[]=54&indblock[]=55&indblock[]=56&indblock[]=57&indblock[]=58&indblock[]=59&indhtyp[]=a&indlen[]=a&indtoil[]=a&msort[]=4&paging[]=80',
          'data'  => array('region_id' => 47, 'user_id' => 10295, 'type' => 'apartament-rent')
        ),
      ),
      'house-rent' => array(
        0 => array(
          'url' => 'http://www.bsn.ru/estate/country/rent/search/results/page1/?b_cost_a=20&indagency_country[]=a&indhel[]=a&indhmat[]=a&indhwat[]=a&indlen[]=a&indlenobl[]=a&indpercent[]=a&indtypv[]=buildings&msort[]=4&paging[]=80',
          'data'  => array('region_id' => 47, 'user_id' => 10295, 'type' => 'house-rent')
        ),
      ),
      'commercial-sale' => array(
        0 => array(
          'url'   => 'http://www.bsn.ru/estate/commercial/sell/search/results/page1/?b_cost=400&ch_block=on&indagency_com[]=a&indblock[]=2&indblock[]=3&indblock[]=4&indblock[]=5&indblock[]=6&indblock[]=7&indblock[]=8&indblock[]=10&indblock[]=11&indblock[]=12&indblock[]=13&indblock[]=15&indblock[]=16&indnazn[]=6&msort[]=8&paging[]=80',
          'data'  => array('region_id' => 78, 'user_id' => 10295, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Офис'))
        ),
        1 => array(
          'url'   => 'http://www.bsn.ru/estate/commercial/sell/search/results/page1/?b_cost=400&ch_block=on&indagency_com[]=a&indblock[]=2&indblock[]=3&indblock[]=4&indblock[]=5&indblock[]=6&indblock[]=7&indblock[]=8&indblock[]=10&indblock[]=11&indblock[]=12&indblock[]=13&indblock[]=15&indblock[]=16&indnazn[]=20&msort[]=8&paging[]=80',
          'data'  => array('region_id' => 78, 'user_id' => 10295, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Отд. стоящее здание'))
        ),
        2 => array(
          'url'   => 'http://www.bsn.ru/estate/commercial/sell/search/results/page1/?b_cost=400&ch_block=on&indagency_com[]=a&indblock[]=2&indblock[]=3&indblock[]=4&indblock[]=5&indblock[]=6&indblock[]=7&indblock[]=8&indblock[]=10&indblock[]=11&indblock[]=12&indblock[]=13&indblock[]=15&indblock[]=16&indnazn[]=17&msort[]=8&paging[]=80',
          'data'  => array('region_id' => 78, 'user_id' => 10295, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Склад'))
        ),
        3 => array(
          'url'   => 'http://www.bsn.ru/estate/commercial/sell/search/results/page1/?b_cost=400&ch_block=on&indagency_com[]=a&indblock[]=2&indblock[]=3&indblock[]=4&indblock[]=5&indblock[]=6&indblock[]=7&indblock[]=8&indblock[]=10&indblock[]=11&indblock[]=12&indblock[]=13&indblock[]=15&indblock[]=16&indnazn[]=14&msort[]=8&paging[]=80',
          'data'  => array('region_id' => 78, 'user_id' => 10295, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Свободного назначения'))
        ),
        4 => array(
          'url'   => 'http://www.bsn.ru/estate/commercial/sell/search/results/page1/?b_cost=400&ch_block=on&indagency_com[]=a&indblock[]=2&indblock[]=3&indblock[]=4&indblock[]=5&indblock[]=6&indblock[]=7&indblock[]=8&indblock[]=10&indblock[]=11&indblock[]=12&indblock[]=13&indblock[]=15&indblock[]=16&indnazn[]=15&msort[]=8&paging[]=80',
          'data'  => array('region_id' => 78, 'user_id' => 10295, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Объекты бытовых услуг'))
        ),
        5 => array(
          'url'   => 'http://www.bsn.ru/estate/commercial/sell/search/results/page1/?b_cost=10&ch_block=on&indagency_com[]=a&indblock[]=2&indblock[]=3&indblock[]=4&indblock[]=5&indblock[]=6&indblock[]=7&indblock[]=8&indblock[]=10&indblock[]=11&indblock[]=12&indblock[]=13&indblock[]=15&indblock[]=16&indnazn[]=21&msort[]=8&paging[]=80',
          'data'  => array('region_id' => 78, 'user_id' => 10295, 'type' => 'commercial-sale', 'params' => array('Тип недвижимости' => 'Земля'))
        ),
        6 => array(
          'url'   => 'http://www.bsn.ru/estate/commercial/sell/search/results/page1/?b_cost=160&indagency_com[]=a&indagency_com[]=a&indblock[]=18&indblock[]=19&indblock[]=20&indblock[]=21&indblock[]=22&indblock[]=23&indblock[]=24&indblock[]=25&indblock[]=26&indblock[]=27&indblock[]=28&indblock[]=29&indblock[]=30&indblock[]=31&indblock[]=32&indblock[]=33&indblock[]=34&indblock[]=35&indblock[]=36&indblock[]=37&indblock[]=38&indblock[]=39&indblock[]=40&indblock[]=41&indblock[]=42&indblock[]=43&indblock[]=44&indblock[]=45&indblock[]=46&indblock[]=47&indblock[]=48&indblock[]=49&indblock[]=50&indblock[]=51&indblock[]=52&indblock[]=53&indblock[]=54&indblock[]=55&indblock[]=56&indblock[]=57&indblock[]=58&indblock[]=59&indnazn[]=a&msort[]=8&paging[]=80',
          'data'  => array('region_id' => 47, 'user_id' => 10295, 'type' => 'commercial-sale')
        ),
      ),
      'commercial-rent' => array(
        0 => array(
          'url'   => 'http://www.bsn.ru/estate/commercial/rent/search/results/page1/?ch_block=on&indagency_com[]=a&indblock[]=2&indblock[]=3&indblock[]=4&indblock[]=5&indblock[]=6&indblock[]=7&indblock[]=8&indblock[]=10&indblock[]=11&indblock[]=12&indblock[]=13&indblock[]=15&indblock[]=16&indnazn[]=6&msort[]=8&paging[]=80',
          'data'  => array('region_id' => 78, 'user_id' => 10295, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Офис'))
        ),
        1 => array(
          'url'   => 'http://www.bsn.ru/estate/commercial/rent/search/results/page1/?ch_block=on&indagency_com[]=a&indblock[]=2&indblock[]=3&indblock[]=4&indblock[]=5&indblock[]=6&indblock[]=7&indblock[]=8&indblock[]=10&indblock[]=11&indblock[]=12&indblock[]=13&indblock[]=15&indblock[]=16&indnazn[]=20&msort[]=8&paging[]=80',
          'data'  => array('region_id' => 78, 'user_id' => 10295, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Отд. стоящее здание'))
        ),
        2 => array(
          'url'   => 'http://www.bsn.ru/estate/commercial/rent/search/results/page1/?ch_block=on&indagency_com[]=a&indblock[]=2&indblock[]=3&indblock[]=4&indblock[]=5&indblock[]=6&indblock[]=7&indblock[]=8&indblock[]=10&indblock[]=11&indblock[]=12&indblock[]=13&indblock[]=15&indblock[]=16&indnazn[]=17&msort[]=8&paging[]=80',
          'data'  => array('region_id' => 78, 'user_id' => 10295, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Склад'))
        ),
        3 => array(
          'url'   => 'http://www.bsn.ru/estate/commercial/rent/search/results/page1/?ch_block=on&indagency_com[]=a&indblock[]=2&indblock[]=3&indblock[]=4&indblock[]=5&indblock[]=6&indblock[]=7&indblock[]=8&indblock[]=10&indblock[]=11&indblock[]=12&indblock[]=13&indblock[]=15&indblock[]=16&indnazn[]=14&msort[]=8&paging[]=80',
          'data'  => array('region_id' => 78, 'user_id' => 10295, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Свободного назначения'))
        ),
        4 => array(
          'url'   => 'http://www.bsn.ru/estate/commercial/rent/search/results/page1/?ch_block=on&indagency_com[]=a&indblock[]=2&indblock[]=3&indblock[]=4&indblock[]=5&indblock[]=6&indblock[]=7&indblock[]=8&indblock[]=10&indblock[]=11&indblock[]=12&indblock[]=13&indblock[]=15&indblock[]=16&indnazn[]=15&msort[]=8&paging[]=80',
          'data'  => array('region_id' => 78, 'user_id' => 10295, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Объекты бытовых услуг'))
        ),
        5 => array(
          'url'   => 'http://www.bsn.ru/estate/commercial/rent/search/results/page1/?ch_block=on&indagency_com[]=a&indblock[]=2&indblock[]=3&indblock[]=4&indblock[]=5&indblock[]=6&indblock[]=7&indblock[]=8&indblock[]=10&indblock[]=11&indblock[]=12&indblock[]=13&indblock[]=15&indblock[]=16&indnazn[]=21&msort[]=8&paging[]=80',
          'data'  => array('region_id' => 78, 'user_id' => 10295, 'type' => 'commercial-rent', 'params' => array('Тип недвижимости' => 'Земля'))
        ),
        6 => array(
          'url'   => 'http://www.bsn.ru/estate/commercial/rent/search/results/page1/?indagency_com[]=a&indagency_com[]=a&indblock[]=18&indblock[]=19&indblock[]=20&indblock[]=21&indblock[]=22&indblock[]=23&indblock[]=24&indblock[]=25&indblock[]=26&indblock[]=27&indblock[]=28&indblock[]=29&indblock[]=30&indblock[]=31&indblock[]=32&indblock[]=33&indblock[]=34&indblock[]=35&indblock[]=36&indblock[]=37&indblock[]=38&indblock[]=39&indblock[]=40&indblock[]=41&indblock[]=42&indblock[]=43&indblock[]=44&indblock[]=45&indblock[]=46&indblock[]=47&indblock[]=48&indblock[]=49&indblock[]=50&indblock[]=51&indblock[]=52&indblock[]=53&indblock[]=54&indblock[]=55&indblock[]=56&indblock[]=57&indblock[]=58&indblock[]=59&indnazn[]=a&msort[]=8&paging[]=80',
          'data'  => array('region_id' => 47, 'user_id' => 10295, 'type' => 'commercial-rent')
        ),
      ),
    );

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->namespace = 'fetch';
    $this->name = 'bsn';
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
    $division = 1000;
    $params = '';
    if ($options['region_id'] == 78) {
      for ($i=2; $i<=16; $i++) {
        if ($i == 9 || $i == 14) continue;
        $params .= '&indblock[]='.$i;
      }
    } elseif ($options['region_id'] == 47) {
      for ($i=18; $i<=59; $i++) {
        $params .= '&indblock[]='.$i;
      }
    }

    switch ($options['type']) {
      case 'apartament-sale':
        $increment  = 200000;
        $max_price  = 20000000;

        $offer_type = 'live';
        $oper = 'sell';
        $params .= '&floor[]=a&from[]=F&indagency_live[]=a&indhtyp[]=a&indlen[]=a&indsubw[]=a&idate[]=0&no_limit=0&checks=';
        break;
      
      case 'apartament-rent':
        if ($options['region_id'] == 47)
          return $this->getLinkFromSettings($options, $worker);

        $increment  = 5000;
        $max_price  = 200000;
        $division   = 1;

        $offer_type = 'live';
        $oper = 'rent';
        $params .= '&from[]=F&indsubw[]=a&indhtyp[]=a&indlen[]=a&indtoil[]=a&indagency_live[]=a';
        break;

      case 'house-sale':
        if ($worker > 31 || $options['region_id'] == 78) return 8;
        if (in_array($worker, array(1,7,25,26))) return false;

        $link = sprintf('http://www.bsn.ru/estate/country/sell/search/results/page1?b_cost=%d&b_fsqear=%d&indlenobl[]=%d&indagency_country[]=a&indhel[]=a&indhmat[]=a&indhwat[]=a&indlen[]=a&indpercent[]=a&indtypv[]=buildings&msort[]=4&paging[]=80',
                    ParseTools::getMinPrice($options['type'], $options['region_id'])/$division, ParseTools::MIN_S, $worker);

        $settings = array(
          'url'   => $link,
          'data'  => array('region_id' => $options['region_id'], 'user_id' => 10295, 'type' => $options['type']),
          'limit' => $options['limit'],
        );

        return $settings;

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

    $link = sprintf('http://www.bsn.ru/estate/%s/%s/search/results/page1/?b_cost=%d&e_cost=%d&b_fsqear=%d%s&msort[]=4&paging[]=80',
                    $offer_type, $oper, $price1/$division, $price2/$division, ParseTools::MIN_S, $params);
    
    $settings = array(
      'url'   => $link,
      'data'  => array('region_id' => $options['region_id'], 'user_id' => 10295, 'type' => $options['type']),
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
      'resource'  => 'Bsn',
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

    $fetcher = new Fetcher_Bsn($settings);
    $fetcher->get();

    ParseLogger::writeFinish($fetcher->lots_parsed, $fetcher->lots_fetched);
    
    unset($fetcher);
  }
}
