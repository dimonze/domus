<?php
/**
 * Fetch lots from http://realty.mail.ru/
 *
 * @package    domus
 * @subpackage task
 */
class fetchMailTask extends sfBaseTask
{
  protected
    $config = null;

  private
    $price1 = null,
    $price2 = null,

    $settings = array(
      'apartament-sale' => array(
        0 => array(
          'url'   => 'http://realty.mail.ru/msk/res/flat/?page=1&from_pr=900000;to_pr=2200000;priceunit=1;inobl=0;use_inobl=1;capital_id=31700;is_room=1;source2_private=1;source2_realtor=1;from_common_area=8;=%CF%EE%E8%F1%EA;spec=1;sort_type=sort_type_date;sort_dir=sort_dir_desc',
          'data'  => array('region_id' => 77, 'user_id' => 2, 'type' => 'apartament-sale'),
          'note'  => 'sale rooms in msk'
        ),
        1 => array(
          'url'   => 'http://realty.mail.ru/msk/res/flat/?page=1&from_pr=2200001;to_pr=4000000;priceunit=1;inobl=0;use_inobl=1;capital_id=31700;is_room=1;source2_private=1;source2_realtor=1;from_common_area=8;=%CF%EE%E8%F1%EA;spec=1;sort_type=sort_type_date;sort_dir=sort_dir_desc',
          'data'  => array('region_id' => 77, 'user_id' => 2, 'type' => 'apartament-sale'),
          'note'  => 'sale rooms in msk'
        ),
        2 => array(
          'url'   => 'http://realty.mail.ru/msk/res/flat/?page=1&from_pr=300000;priceunit=1;inobl=1;use_inobl=1;capital_id=31700;is_room=1;source2_private=1;source2_realtor=1;from_common_area=8;=%CF%EE%E8%F1%EA;spec=1;sort_type=sort_type_date;sort_dir=sort_dir_desc',
          'data'  => array('region_id' => 50, 'user_id' => 2, 'type' => 'apartament-sale'),
          'node'  => 'sale rooms in mo'
        ),
      ),
      'apartament-rent' => array(
        0 => array(
          'url'   => 'http://realty.mail.ru/msk/res/rflat/?page=1&from_pr=5000;priceunit=1;inobl=0;use_inobl=1;capital_id=31700;is_room=1;source2_private=1;source2_realtor=1;from_common_area=8;=%CF%EE%E8%F1%EA;spec=1;sort_type=sort_type_date;sort_dir=sort_dir_desc',
          'data'  => array('region_id' => 77, 'user_id' => 2, 'type' => 'apartament-rent'),
          'note'  => 'rent rooms in msk'
        ),
      ),
      'commercial-sale' => array(
        0 => array(
          'url'   => 'http://realty.mail.ru/msk/res/office/?page=1&from_pr=50000;priceunit=1;inobl=0;use_inobl=1;capital_id=31700;source2_private=1;source2_realtor=1;from_common_area=8;=%CF%EE%E8%F1%EA;spec=1;sort_type=sort_type_date;sort_dir=sort_dir_desc',
          'data'  => array('region_id' => 77, 'user_id' => 2, 'type' => 'commercial-sale')
        ),
        1 => array(
          'url'   => 'http://realty.mail.ru/msk/res/office/?page=1&from_pr=20000;priceunit=1;inobl=1;use_inobl=1;capital_id=31700;source2_private=1;source2_realtor=1;from_common_area=8;=%CF%EE%E8%F1%EA;spec=1;sort_type=sort_type_date;sort_dir=sort_dir_desc',
          'data'  => array('region_id' => 50, 'user_id' => 2, 'type' => 'commercial-sale')
        ),
        2 => array(
          'url'   => 'http://realty.mail.ru/spb/res/office/?page=1&types=2010;types=2020;types=2030;types=2040;types=2120;from_pr=20000;priceunit=1;inobl=1;use_inobl=1;capital_id=50020;from_common_area=8;spec=1;sort_type=sort_type_date;sort_dir=sort_dir_desc',
          'data'  => array('region_id' => 47, 'user_id' => 2, 'type' => 'commercial-sale')
        ),
        3 => array(
          'url'   => 'http://realty.mail.ru/nnov/res/office/?page=1&types=2010;types=2020;types=2030;types=2040;types=2120;from_pr=20000;to_pr=10000000;priceunit=1;inobl=0;use_inobl=1;capital_id=760160;from_common_area=8;spec=1;sort_type=sort_type_date;sort_dir=sort_dir_desc',
          'data'  => array('region_id' => 52, 'user_id' => 2, 'type' => 'commercial-sale')
        ),
        4 => array(
          'url'   => 'http://realty.mail.ru/nnov/res/office/?page=1&types=2010;types=2020;types=2030;types=2040;types=2120;from_pr=10000001;priceunit=1;inobl=0;use_inobl=1;capital_id=760160;from_common_area=8;spec=1;sort_type=sort_type_date;sort_dir=sort_dir_desc',
          'data'  => array('region_id' => 52, 'user_id' => 2, 'type' => 'commercial-sale')
        ),
        5 => array(
          'url'   => 'http://realty.mail.ru/nnov/res/office/?page=1&types=2010;types=2020;types=2030;types=2040;types=2120;from_pr=20000;priceunit=1;inobl=1;use_inobl=1;capital_id=760160;from_common_area=8;spec=1;sort_type=sort_type_date;sort_dir=sort_dir_desc',
          'data'  => array('region_id' => 52, 'user_id' => 2, 'type' => 'commercial-sale')
        ),
      ),
      'commercial-rent' => array(
        0 => array(
          'url'   => 'http://realty.mail.ru/msk/res/roffice/?page=1&from_pr=3000;to_pr=100000;priceunit=1;inobl=0;use_inobl=1;capital_id=31700;source2_private=1;source2_realtor=1;from_common_area=8;=%CF%EE%E8%F1%EA;spec=1;sort_type=sort_type_date;sort_dir=sort_dir_desc',
          'data'  => array('region_id' => 77, 'user_id' => 2, 'type' => 'commercial-rent')
        ),
        1 => array(
          'url'   => 'http://realty.mail.ru/msk/res/roffice/?page=1&from_pr=100001;to_pr=400000;priceunit=1;inobl=0;use_inobl=1;capital_id=31700;source2_private=1;source2_realtor=1;from_common_area=8;=%CF%EE%E8%F1%EA;spec=1;sort_type=sort_type_date;sort_dir=sort_dir_desc',
          'data'  => array('region_id' => 77, 'user_id' => 2, 'type' => 'commercial-rent')
        ),
        2 => array(
          'url'   => 'http://realty.mail.ru/msk/res/roffice/?page=1&from_pr=400001;priceunit=1;inobl=0;use_inobl=1;capital_id=31700;source2_private=1;source2_realtor=1;from_common_area=8;=%CF%EE%E8%F1%EA;spec=1;sort_type=sort_type_date;sort_dir=sort_dir_desc',
          'data'  => array('region_id' => 77, 'user_id' => 2, 'type' => 'commercial-rent')
        ),
        3 => array(
          'url'   => 'http://realty.mail.ru/msk/res/roffice/?page=1&from_pr=20000;priceunit=1;inobl=1;use_inobl=1;capital_id=31700;source2_private=1;source2_realtor=1;from_common_area=8;=%CF%EE%E8%F1%EA;spec=1;sort_type=sort_type_date;sort_dir=sort_dir_desc',
          'data'  => array('region_id' => 50, 'user_id' => 2, 'type' => 'commercial-rent'),
        ),
        4 => array(
          'url'   => 'http://realty.mail.ru/spb/res/roffice/?page=1&from_pr=20000;priceunit=1;inobl=1;use_inobl=1;capital_id=50020;from_common_area=8;spec=1;sort_type=sort_type_date;sort_dir=sort_dir_desc',
          'data'  => array('region_id' => 47, 'user_id' => 2, 'type' => 'commercial-rent'),
        ),
        5 => array(
          'url'   => 'http://realty.mail.ru/nnov/res/roffice/?page=1&from_pr=20000;to_pr=50000;priceunit=1;inobl=0;use_inobl=1;capital_id=760160;from_common_area=8;spec=1;sort_type=sort_type_date;sort_dir=sort_dir_desc',
          'data'  => array('region_id' => 52, 'user_id' => 2, 'type' => 'commercial-rent'),
        ),
        6 => array(
          'url'   => 'http://realty.mail.ru/nnov/res/roffice/?page=1&from_pr=50001;to_pr=140000;priceunit=1;inobl=0;use_inobl=1;capital_id=760160;from_common_area=8;spec=1;sort_type=sort_type_date;sort_dir=sort_dir_desc',
          'data'  => array('region_id' => 52, 'user_id' => 2, 'type' => 'commercial-rent'),
        ),
        7 => array(
          'url'   => 'http://realty.mail.ru/nnov/res/roffice/?page=1&from_pr=140001;priceunit=1;inobl=0;use_inobl=1;capital_id=760160;from_common_area=8;spec=1;sort_type=sort_type_date;sort_dir=sort_dir_desc',
          'data'  => array('region_id' => 52, 'user_id' => 2, 'type' => 'commercial-rent'),
        ),
        8 => array(
          'url'   => 'http://realty.mail.ru/nnov/res/roffice/?page=1&from_pr=20000;priceunit=1;inobl=1;use_inobl=1;capital_id=760160;from_common_area=8;spec=1;sort_type=sort_type_date;sort_dir=sort_dir_desc',
          'data'  => array('region_id' => 52, 'user_id' => 2, 'type' => 'commercial-rent'),
        ),
      ),
    );

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->namespace = 'fetch';
    $this->name = 'mail';
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
    $region_options = array(
      77  => array('region' => 'msk', 'inobl' => 0, 'capital_id' => 31700),
      50  => array('region' => 'msk', 'inobl' => 1, 'capital_id' => 31700),
      78  => array('region' => 'spb', 'inobl' => 0, 'capital_id' => 50020),
      47  => array('region' => 'spb', 'inobl' => 1, 'capital_id' => 50020),
      52  => array('region' => 'nnov', 'inobl' => 0, 'capital_id' => 760160),
      520 => array('region' => 'nnov', 'inobl' => 1, 'capital_id' => 760160),
    );

    switch ($options['type']) {
      case 'apartament-sale':
        $increment  = $options['region_id'] == 77 ? 200000 : 500000;
        $max_price  = 20000000;

        $offer_type = 'flat';
        $params = ';rooms=1;rooms=2;rooms=3;rooms=4;rooms=5';
        break;

      case 'apartament-rent':
        $increment  = $options['region_id'] == 77 ? 5000 : 10000;
        $max_price  = 20000/*100000*/;

        $offer_type = 'rflat';
        $params = ';rooms=1;rooms=2;rooms=3;rooms=4;rooms=5';
        break;

      case 'house-sale':
        $increment  = 500000;
        $max_price  = 50000000;

        $offer_type = 'country';
        $params = ';types=3010;types=3020;types=3030;types=3040;types=3050;types=3060';
        break;

      case 'house-rent':
        $increment  = 20000;
        $max_price  = 500000;

        $offer_type = 'rcountry';
        $params = ';types=3010;types=3020;types=3030;types=3040;types=3050;types=3060';
        break;

      case 'commercial-sale':
        if ($options['region_id'] != 78)
          return $this->getLinkFromSettings($options, $worker);

        $increment  = 500000;
        $max_price  = 20000000;

        $offer_type = 'office';
        $params = ';types=2010;types=2020;types=2030;types=2040;types=2120';
        break;

      case 'commercial-rent':
        if ($options['region_id'] != 78)
          return $this->getLinkFromSettings($options, $worker);

        $increment  = 20000;
        $max_price  = 2000000;

        $offer_type = 'office';
        $params = ';types=2010;types=2020;types=2030;types=2040;types=2120';

      default:
        return $this->getLinkFromSettings($options, $worker);
    }

    if (!isset($region_options[$options['region_id']], $increment, $max_price, $offer_type, $params)) return false;

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

    $link = sprintf('http://realty.mail.ru/%s/res/%s/?page=1&from_pr=%d;to_pr=%d;priceunit=1;inobl=%d;use_inobl=1;capital_id=%d%s;from_common_area=%d;spec=1;sort_type=sort_type_date;sort_dir=sort_dir_desc',
                    $region_options[$options['region_id']]['region'],
                    $offer_type, $price1, $price2,
                    $region_options[$options['region_id']]['inobl'],
                    $region_options[$options['region_id']]['capital_id'],
                    $params, ParseTools::MIN_S);

    $settings = array(
      'url'   => $link,
      'data'  => array('region_id' => $options['region_id'], 'user_id' => 2, 'type' => $options['type']),
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
      'resource'  => 'Mail',
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

    $fetcher = new Fetcher_Mail($settings);
    $fetcher->get();

    ParseLogger::writeFinish($fetcher->lots_parsed, $fetcher->lots_fetched);

    unset($fetcher);
  }
}
