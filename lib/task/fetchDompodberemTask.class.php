<?php
/**
 * Fetch lots from vsenovostroyki
 *
 * @package    domus
 * @subpackage task
 */
class fetchDompodberemTask extends sfBaseTask
{
  const USER_ID = 41729;

  private
    $settings = array(
      0 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=1&ploshaddomamax=1&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Дом/Коттедж')),
      ),
      1 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=1&ploshaddomamax=2&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Дом/Коттедж')),
      ),
      2 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=1&ploshaddomamax=3&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Дом/Коттедж')),
      ),
      3 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=1&ploshaddomamax=4&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Дом/Коттедж')),
      ),
      4 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=1&ploshaddomamax=5&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Дом/Коттедж')),
      ),

      5 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=2&ploshaddomamax=1&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Таунхаусы и Дуплексы')),
      ),
      6 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=2&ploshaddomamax=2&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Таунхаусы и Дуплексы')),
      ),
      7 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=2&ploshaddomamax=3&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Таунхаусы и Дуплексы')),
      ),
      8 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=2&ploshaddomamax=4&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Таунхаусы и Дуплексы')),
      ),
      9 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=2&ploshaddomamax=5&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Таунхаусы и Дуплексы')),
      ),


      10 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=1&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),
      11 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=2&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),
      12 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=3&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),
      13 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=4&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),
      14 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=5&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),
      15 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=6&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),
      16 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=7&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),
      17 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=8&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),
      18 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=9&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),
      19 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=10&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),
      20 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=11&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),
      21 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=12&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),
      22 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=13&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),
      23 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=14&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),
      24 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=15&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),
      25 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=16&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),
      26 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=17&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),
      27 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=18&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),
      28 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=19&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),
      29 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=20&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),
      30 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=21&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),
      31 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=22&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),
      32 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=23&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),
      33 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=24&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),
      34 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=25&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),
      35 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=26&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),
      36 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=27&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),
      37 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=4&highway%5B0%5D=28&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок')),
      ),

      38 => array(
        'url' => 'http://dompodberem.ru/?do=cottage&action=search&cotsort=0&ordertp=asc&tp%5B0%5D=3&second=on',
        'data' => array('region_id' => 50, 'user_id' => self::USER_ID, 'type' => 'cottage-sale', 'params' => array('Тип предложения' => 'Участок с подрядом')),
      ),
    );


  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->namespace = 'fetch';
    $this->name = 'dompodberem';
    $this->briefDescription = null;
    $this->detailedDescription = null;

    $this->addOption('worker', null, sfCommandOption::PARAMETER_OPTIONAL, 'worker #', null);
    $this->addOption('limit', null, sfCommandOption::PARAMETER_OPTIONAL, 'limit lots count', 300);
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);

    ini_set('memory_limit', '768M');
    $log_options = array(
      'resource'  => 'Dompodberem',
      'type'      => '',
      'limit'     => $options['limit'],
    );

    if (!empty($options['worker'])) {
      if (!empty($this->settings[$options['worker']-1])) {
        $settings = $this->settings[$options['worker']-1];
        $settings = array_merge(array('limit' => $options['limit']), $settings);

        $this->runFetcher($log_options, $settings);
      }
    }
    else {
      foreach ($this->settings as $settings) {
        $settings['limit'] = $options['limit'];
        $this->runFetcher($log_options, $settings);
      }
    }
  }


  private function runFetcher($log_options, $settings)
  {
    $log_options['page'] = $settings['url'];
    ParseLogger::initLogger($log_options);

    $fetcher = new Fetcher_Dompodberem($settings);
    $fetcher->get();

    ParseLogger::writeFinish($fetcher->lots_parsed, $fetcher->lots_fetched);

    unset($fetcher);
  }
}
