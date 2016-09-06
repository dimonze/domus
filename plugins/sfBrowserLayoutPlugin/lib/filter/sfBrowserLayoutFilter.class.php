<?php
/**
 * sfBrowserLayoutPlugin provides a mechanism for splitting stylesheets and
 * javascripts for browsers
 *
 * @package    sfBrowserLayoutPlugin
 * @author     Eugeniy Belyaev <eugeiy.b@garin-studio.ru>
 */
class sfBrowserLayoutFilter extends sfFilter
{

  static $condition = array(
    'ff' => 'firefox',
    'ff2' => 'firefox/2',
    'ff3' => 'firefox/3',
    'ie6' => 'msie 6',
    'ie7' => 'msie 7',
    'ie8' => 'msie 8',
    'opera' => 'opera',
    'safari' => 'safari'
  );

  public function execute($filterChain)
  {
    $request = $this->getContext()->getRequest();
    $response = $this->getContext()->getResponse();

    foreach (sfYaml::load(sfConfig::get('sf_app_dir') . '/config/view.yml') as $name => $config) {
      if (isset(self::$condition[$name])) {
        if (preg_match('~' . self::$condition[$name] . '~i', $request->getHttpHeader('User-Agent'))) {
          $this->append($response, $config);
        }
      }
    }
    $filterChain->execute();
  }

  private function append(sfWebResponse $response, $config)
  {
    if (isset($config['stylesheets'])) {
      foreach ($config['stylesheets'] as $stylesheet) {
        $response->addStylesheet($stylesheet, 'last');
      }
    }
    if (isset($config['javascripts'])) {
      foreach ($config['javascripts'] as $javascript) {
        $response->addJavascript($javascript, 'last');
      }
    }
  }

}