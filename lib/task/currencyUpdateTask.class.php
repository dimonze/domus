<?php
/**
 * Updates currency
 *
 * @package    domus
 * @subpackage task
 */
class currencyUpdateTask extends sfBaseTask
{
  protected
    $config = null;

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->namespace = 'domus';
    $this->name = 'currency-update';
    $this->briefDescription = 'Updates currency rates from cbr.ru';
    $this->detailedDescription = '';
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $app_config = sfYaml::load(sfConfig::get('sf_apps_dir') . '/frontend/config/app.yml');
    $config = &$app_config['all']['exchange']['rates'];

    if (!$app_config['all']['exchange']['update'])
    {
      $this->log('Updating is disabled');
    }
    else {
      $this->log(self::doFetch($config));
      file_put_contents(sfConfig::get('sf_apps_dir') . '/frontend/config/app.yml', sfYaml::dump($app_config));
      $this->getFilesystem()->remove(sfFinder::type('file')->name('config_app.yml.php')->in(sfConfig::get('sf_cache_dir')));
    }

    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'dev', true);
    $context = sfContext::createInstance($configuration);
    $databaseManager = new sfDatabaseManager($configuration);

    $this->log(self::doUpdateDB($config));
  }

  public static function doFetch(&$config)
  {
    $log = array();

    $list = new DOMDocument();
    $list->load('http://www.cbr.ru/scripts/XML_daily.asp?date_req='.date('d/m/Y'));
    foreach ($list->getElementsByTagName('Valute') as $valute)
    {
      $rate = 1;
      $code = '';
      foreach ($valute->childNodes as $param)
      {
        if ($param->nodeName == 'CharCode')
        {
          $code = $param->nodeValue;
        }
        elseif ($param->nodeName == 'Nominal')
        {
          $rate /= $param->nodeValue;
        }
        elseif ($param->nodeName == 'Value')
        {
          $rate *= (float) str_replace(',', '.', $param->nodeValue);
        }
      }
      if (array_key_exists($code, $config))
      {
        $log[] = sprintf('%s [%f] => %f', $code, $config[$code], $rate);
        $config[$code] = $rate;
      }
    }
    return $log;
  }

  public static function doUpdateDB($config)
  {
    $conn = Doctrine_Manager::connection();
    $log = array();
    foreach ($config as $code => $rate)
    {
      $count = Doctrine_Query::create()->update('Lot l')->set('exchange', '?', $rate)
        ->where('currency = ?', $code)
        ->andWhere('status = ?','active')
        ->execute();
      $log[] = sprintf('Updated %d rows for %s', $count, $code);
    }
    return $log;
  }

}
