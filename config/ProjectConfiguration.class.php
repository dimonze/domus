<?php

require_once dirname(__FILE__).'/../lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
  static protected
    $zendLoaded = false,
    $simpleHtmlDomLoaded = false;
  protected
    $masterConnection = null,
    $slaveConnection  = null;

  public function setup()
  {
    mb_internal_encoding('UTF-8');
    $this->registerErrbit();
    $this->enableAllPluginsExcept(array('sfPropelPlugin'));
  }

  protected function registerErrbit()
  {
    // Production only
    if (strpos(__DIR__, 'mesto.ru')) {
      require_once sfConfig::get('sf_lib_dir') . '/vendor/errbit/lib/Errbit.php';
      Errbit::instance()
        ->configure(array(
          'api_key'           => '5b8deb323936fd0c1b685bc1e22e9e59',
          'host'              => 'errbit.garin.su',
          'secure'            => true,
          'project_root'      => sfConfig::get('sf_root_dir'),
          'environment_name'  => 'production',
        ))
        ->start(array('fatal'));

      $this->getEventDispatcher()->connect('application.throw_exception', function(sfEvent $e) {
        Errbit::instance()->notify($e->getSubject());
      });
    }
  }


  static public function registerSimpleHtmlDom()
  {
    if (self::$simpleHtmlDomLoaded) {
      return;
    }
    set_include_path(sfConfig::get('sf_lib_dir').'/vendor/SimpleHTMLDOM'.PATH_SEPARATOR.get_include_path());
    require_once 'SimpleHTMLDOM.php';
    require_once 'SimpleHTMLDOMNode.php';
    self::$simpleHtmlDomLoaded = true;
  }

  static public function registerZend()
  {
    if (self::$zendLoaded) {
      return;
    }

    set_include_path(sfConfig::get('sf_lib_dir').'/vendor'.PATH_SEPARATOR.get_include_path());
    require_once sfConfig::get('sf_lib_dir').'/vendor/Zend/Loader/Autoloader.php';
    Zend_Loader_Autoloader::getInstance();
    self::$zendLoaded = true;
  }

  public function initializeConnections()
  {
    $slaves = array();
    foreach (Doctrine_Manager::getInstance()->getConnections() as $name => $conn)
    {
      switch (true)
      {
        case 'doctrine' == $name:
          $this->masterConnection = $conn;
          break;
        case 0 === strpos($name, 'slave'):
          $slaves[] = $conn;
          break;
      }
    }

    if (is_null($this->masterConnection))
    {
      $this->masterConnection = Doctrine_Manager::connection();
    }

    // choose one slave connection for the current request
    $this->slaveConnection = count($slaves) ?
      $slaves[rand(0, count($slaves) - 1)] :
      Doctrine_Manager::connection();
  }

  public function getMasterConnection()
  {
    $this->masterConnection || $this->initializeConnections();
    return $this->masterConnection;
  }

  public function getSlaveConnection()
  {
    $this->slaveConnection || $this->initializeConnections();
    return $this->slaveConnection;
  }
}
