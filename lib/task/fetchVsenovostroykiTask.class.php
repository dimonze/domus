<?php
/**
 * Fetch lots from vsenovostroyki
 *
 * @package    domus
 * @subpackage task
 */
class fetchVsenovostroykiTask extends sfBaseTask
{
  private
    $settings = array(
      0 => array(
        'url' => 'http://vsenovostroyki.ru/%D0%BD%D0%BE%D0%B2%D0%BE%D1%81%D1%82%D1%80%D0%BE%D0%B9%D0%BA%D0%B8-%D0%BC%D0%BE%D1%81%D0%BA%D0%B2%D1%8B/?price_to=145000&p=1',
        'data' => array('region_id' => 77, 'user_id' => 33544, 'type' => 'new_building-sale', 'params' => array())
      ),
      1 => array(
        'url' => 'http://vsenovostroyki.ru/%D0%BD%D0%BE%D0%B2%D0%BE%D1%81%D1%82%D1%80%D0%BE%D0%B9%D0%BA%D0%B8-%D0%BC%D0%BE%D1%81%D0%BA%D0%B2%D1%8B/?price_from=145001&price_to=250000&p=1',
        'data' => array('region_id' => 77, 'user_id' => 33544, 'type' => 'new_building-sale', 'params' => array())
      ),
      2 => array(
        'url' => 'http://vsenovostroyki.ru/%D0%BD%D0%BE%D0%B2%D0%BE%D1%81%D1%82%D1%80%D0%BE%D0%B9%D0%BA%D0%B8-%D0%BC%D0%BE%D1%81%D0%BA%D0%B2%D1%8B/?price_from=250001&p=1',
        'data' => array('region_id' => 77, 'user_id' => 33544, 'type' => 'new_building-sale', 'params' => array())
      ),
      
      
      3 => array(
        'url' => 'http://vsenovostroyki.ru/%D0%BD%D0%BE%D0%B2%D0%BE%D1%81%D1%82%D1%80%D0%BE%D0%B9%D0%BA%D0%B8-%D0%BF%D0%BE%D0%B4%D0%BC%D0%BE%D1%81%D0%BA%D0%BE%D0%B2%D1%8C%D1%8F/?price_to=45000&p=1',
        'data' => array('region_id' => 50, 'user_id' => 33544, 'type' => 'new_building-sale', 'params' => array())
      ),
      4 => array(
        'url' => 'http://vsenovostroyki.ru/%D0%BD%D0%BE%D0%B2%D0%BE%D1%81%D1%82%D1%80%D0%BE%D0%B9%D0%BA%D0%B8-%D0%BF%D0%BE%D0%B4%D0%BC%D0%BE%D1%81%D0%BA%D0%BE%D0%B2%D1%8C%D1%8F/?price_from=45001&price_to=55000&p=1',
        'data' => array('region_id' => 50, 'user_id' => 33544, 'type' => 'new_building-sale', 'params' => array())
      ),
      5 => array(
        'url' => 'http://vsenovostroyki.ru/%D0%BD%D0%BE%D0%B2%D0%BE%D1%81%D1%82%D1%80%D0%BE%D0%B9%D0%BA%D0%B8-%D0%BF%D0%BE%D0%B4%D0%BC%D0%BE%D1%81%D0%BA%D0%BE%D0%B2%D1%8C%D1%8F/?price_from=55001&price_to=70000&p=1',
        'data' => array('region_id' => 50, 'user_id' => 33544, 'type' => 'new_building-sale', 'params' => array())
      ),
      6 => array(
        'url' => 'http://vsenovostroyki.ru/%D0%BD%D0%BE%D0%B2%D0%BE%D1%81%D1%82%D1%80%D0%BE%D0%B9%D0%BA%D0%B8-%D0%BF%D0%BE%D0%B4%D0%BC%D0%BE%D1%81%D0%BA%D0%BE%D0%B2%D1%8C%D1%8F/?price_from=70001&p=1',
        'data' => array('region_id' => 50, 'user_id' => 33544, 'type' => 'new_building-sale', 'params' => array())
      ),
      
      7 => array(
        'url' => 'http://vsenovostroyki.ru/%D0%BD%D0%BE%D0%B2%D0%BE%D1%81%D1%82%D1%80%D0%BE%D0%B9%D0%BA%D0%B8-%D1%81%D0%B0%D0%BD%D0%BA%D1%82-%D0%BF%D0%B5%D1%82%D0%B5%D1%80%D0%B1%D1%83%D1%80%D0%B3/?p=1',
        'data' => array('region_id' => 78, 'user_id' => 33544, 'type' => 'new_building-sale', 'params' => array())
      ),
    );


  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->namespace = 'fetch';
    $this->name = 'vsenovostroyki';
    $this->briefDescription = null;
    $this->detailedDescription = null;

    $this->addOption('worker', null, sfCommandOption::PARAMETER_REQUIRED, 'worker #', null);
    $this->addOption('limit', null, sfCommandOption::PARAMETER_OPTIONAL, 'limit lots count', 300);
    $this->addOption('region_id', null, sfCommandOption::PARAMETER_OPTIONAL, 'region number or 0', 0);
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
      'resource'  => 'Vsenovostroyki',
      'type'      => 'new_building-sale',
      'limit'     => $options['limit'],
    );

    if ($options['region_id'] !== 0) {
      foreach ($this->settings as $settings) {
        if ($settings['data']['region_id'] == $options['region_id']) {
          $settings['limit'] = $options['limit'];
          $this->runFetcher($log_options, $settings);
        }
      }
    }
    else {
      if (empty($options['worker'])) throw new Exception('Parameter "worker" is required');

      if (!empty($this->settings[$options['worker']-1])) {
        $settings = $this->settings[$options['worker']-1];
        $settings = array_merge(array('limit' => $options['limit']), $settings);

        $this->runFetcher($log_options, $settings);
      }
    }
  }


  private function runFetcher($log_options, $settings)
  {
    $log_options['page'] = $settings['url'];
    ParseLogger::initLogger($log_options);

    $fetcher = new Fetcher_Vsenovostroyki($settings);
    $fetcher->get();

    ParseLogger::writeFinish($fetcher->lots_parsed, $fetcher->lots_fetched);

    unset($fetcher);
  }
}
