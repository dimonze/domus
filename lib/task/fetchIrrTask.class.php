<?php
/**
 * Fetch lots from iir
 *
 * @package    domus
 * @subpackage task
 */
class fetchIrrTask extends sfBaseTask
{
  protected
    $config = null;

  private
    $price1 = null,
    $price2 = null,

    $settings = array(),

    $regions = array(
      1  => 'adygeya-resp.',
      4  => 'altay-resp.',
      22 => 'altayskiy-kray.',
      28 => 'amurskaya-obl.',
      29 => 'arhangelskaya-obl.',
      30 => 'astrahanskaya-obl.',
      2  => 'bashkortostan-resp.',
      31 => 'belgorodskaya-obl.',
      32 => 'bryanskaya-obl.',
      3  => 'buryatiya-resp.',
      33 => 'vladimirskaya-obl.',
      34 => 'volgogradskaya-obl.',
      35 => 'vologodskaya-obl.',
      36 => 'voronezhskaya-obl.',
      5  => 'dagestan-resp.',
      79 => 'evreyskaya-aobl.',
      75 => 'zabaykalskiy-kray.',
      37 => 'ivanovskaya-obl.',
      6  => 'ingushetiya-resp.',
      38 => 'irkutskaya-obl.',
      7  => 'kabardino-balkarskaya-resp.',
      39 => 'kaliningradskaya-obl.',
      8  => 'kalmykiya-resp.',
      40 => 'kaluzhskaya-obl.',
      41 => 'kamchatskiy-kray.',
      9  => 'karachaevo-cherkesskaya-resp.',
      10 => 'kareliya-resp.',
      42 => 'kemerovskaya-obl.',
      43 => 'kirovskaya-obl.',
      11 => 'komi-resp.',
      44 => 'kostromskaya-obl.',
      23 => 'krasnodarskiy-kray.',
      24 => 'krasnoyarskiy-kray.',
      45 => 'kurganskaya-obl.',
      46 => 'kurskaya-obl.',
      47 => 'saint-petersburg.',
      48 => 'lipetskaya-obl.',
      49 => 'magadanskaya-obl.',
      12 => 'mariy-el-resp.',
      13 => 'mordoviya-resp.',
      77 => '',
      50 => '',
      51 => 'murmanskaya-obl.',
      83 => 'nenetskiy-ao.',
      52 => 'nizhegorodskaya-obl.',
      53 => 'novgorodskaya-obl.',
      54 => 'novosibirskaya-obl.',
      55 => 'omskaya-obl.',
      56 => 'orenburgskaya-obl.',
      57 => 'orlovskaya-obl.',
      58 => 'penzenskaya-obl.',
      59 => 'permskiy-kray.',
      25 => 'primorskiy-kray.',
      60 => 'pskovskaya-obl.',
      61 => 'rostovskaya-obl.',
      62 => 'ryazanskaya-obl.',
      63 => 'samarskaya-obl.',
      78 => 'saint-petersburg.',
      64 => 'saratovskaya-obl.',
      14 => 'saha-yakutiya-resp.',
      65 => 'sahalinskaya-obl.',
      66 => 'sverdlovskaya-obl.',
      15 => 'alaniya-resp.',
      67 => 'smolenskaya-obl.',
      26 => 'stavropolskiy-kray.',
      68 => 'tambovskaya-obl.',
      16 => 'tatarstan-resp.',
      69 => 'tverskaya-obl.',
      70 => 'tomskaya-obl.',
      71 => 'tulskaya-obl.',
      17 => 'tyva-resp.',
      72 => 'tyumenskaya-obl.',
      18 => 'udmurtskaya-resp.',
      73 => 'ulyanovskaya-obl.',
      27 => 'habarovskiy-kray.',
      19 => 'hakasiya-resp.',
      86 => 'yugra-ao.',
      74 => 'chelyabinskaya-obl.',
      95 => 'chechenskaya-resp.',
      21 => 'chuvashskaya-resp.',
      87 => 'chukotskiy-ao.',
      89 => 'yamalo-nenetskiy-ao.',
      76 => 'yaroslavskaya-obl.',
    );


  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->namespace = 'fetch';
    $this->name = 'irr';
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
    switch ($options['type']) {
      case 'apartament-sale':
        $increment  = $options['region_id'] == 77 ? 200000 : 500000;
        $max_price  = 20000000;

        $offer_type = 'apartments-sale';
        $params = '/rooms=1,2,3,4,100';
        break;

      case 'apartament-rent':
        $increment  = $options['region_id'] == 77 ? 5000 : 10000;
        $max_price  = 100000;

        $offer_type = 'rent';
        $params = '/rooms=1,2,3,4,100';
        break;

      case 'house-sale':
        $increment  = 500000;
        $max_price  = 50000000;

        $offer_type = 'out-of-town';
        $params = '';
        break;

      case 'house-rent':
        $increment  = 20000;
        $max_price  = 200000;

        $offer_type = 'out-of-town-rent';
        $params = '';
        break;

      case 'commercial-sale':
        $increment  = $options['region_id'] == 77 ? 10000000 : 25000000;
        $max_price  = 200000000;

        $offer_type = 'commercial-sale';
        $params = '';
        break;

      case 'commercial-rent':
        $increment  = 20000;
        $max_price  = 2000000;

        $offer_type = 'commercial';
        $params = '';
        break;

      default:
        return $this->getLinkFromSettings($options, $worker);
    }

    if (!isset($this->regions[$options['region_id']], $increment, $max_price, $offer_type, $params)) return false;

        if ($options['region_id'] == 77) $offer_type .= '/moskva-gorod';
    elseif ($options['region_id'] == 50) $offer_type .= '/moskovskaya-obl';
    elseif ($options['region_id'] == 78) $offer_type .= '/sankt-peterburg-gorod';
    elseif ($options['region_id'] == 47) $offer_type .= '/leningradskaya-obl';

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

    $link = sprintf('http://%sirr.ru/real-estate/%s/search/currency=RUR%s/meters-total=%s%d.0/price=%s%d%s%d/page_len60/',
                    $this->regions[$options['region_id']], $offer_type, $params, rawurlencode('больше '), ParseTools::MIN_S, rawurlencode('от '), $price1, rawurlencode(' до '), $price2);

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
    throw new Exception('Fetching Irr is temporary disabled');
    if (empty($options['type'])) throw new Exception('Parameter "type" is required');

    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);

    ini_set('memory_limit', '768M');
    $log_options = array(
      'resource'  => 'Irr',
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

    $fetcher = new Fetcher_Irr($settings);
    $fetcher->get();

    ParseLogger::writeFinish($fetcher->lots_parsed, $fetcher->lots_fetched);

    unset($fetcher);
  }
}
