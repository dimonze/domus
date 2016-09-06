<?php
/**
 * Fetch lots from mian
 *
 * @package    domus
 * @subpackage task
 */
class fetchMianTask extends sfGarinTask
{
  protected
    $config = null;

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->namespace = 'fetch';
    $this->name = 'mian';
    $this->briefDescription = null;
    $this->detailedDescription = null;

    $this->addOption('type', null, sfCommandOption::PARAMETER_REQUIRED, 'like apartament-sale', 'apartament-sale');
    $this->addOption('worker', null, sfCommandOption::PARAMETER_REQUIRED, 'worker #', null);
    $this->addOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Limit lots count', 150);
  }

  private $settings = array(
    'commercial-rent' => array(
      0 => array(
        'url' => 'http://www.mian.ru/Moscow/base/SearchController.ashx?search=commercies&render=commercies&result=SearchFormClientID%3AsearchForm%3BRegionID%3A%3B%3B&filter=page:1,2147483647,city_asc;oblast:77;actions:1000;',
        'data' => array('region_id' => 77, 'user_id' => 19, 'type' => 'commercial-rent')
      ),
      1 => array(
        'url' => 'http://www.mian.ru/Moscow/base/SearchController.ashx?search=commercies&render=commercies&result=SearchFormClientID%3AsearchForm%3BRegionID%3A%3B%3B&filter=page:1,2147483647,city_asc;oblast:50;actions:1000;',
        'data' => array('region_id' => 50, 'user_id' => 19, 'type' => 'commercial-rent')
      )
    )
  );


  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);

    if (!empty($this->settings[$options['type']])) {

      if (empty($options['worker'])) {
        ini_set('memory_limit', '512M');
        foreach ($this->settings[$options['type']] as $settings) {
          $settings = array_merge(array('limit' => $options['limit']), $settings);
          $fetcher = new Fetcher_Mian($settings, array($this, 'writeProgress'));
          $fetcher->get();
        }
      }
      elseif (!empty($this->settings[$options['type']][$options['worker'] -1])) {
        ini_set('memory_limit', '300M');
        $settings = $this->settings[$options['type']][$options['worker'] -1];
        $settings = array_merge(array('limit' => $options['limit']), $settings);
        $fetcher = new Fetcher_Mian($settings, array($this, 'writeProgress'));
        $fetcher->get();
      }
    }
  }
}
