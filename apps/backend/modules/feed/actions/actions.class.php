<?php

/**
 * feed actions.
 *
 * @package    domus
 * @subpackage feed
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class feedActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $config = sfYaml::load(sfConfig::get('sf_apps_dir') . '/frontend/config/app.yml');
    $this->settings = $config['all']['rss'];
    if ($request->isMethod('post')) {
      $data = $request->getParameter('settings', array());
      foreach ($this->settings as $param => $field)
      {
        foreach ($field as $name => $value){
          $this->settings[$param][$name] = $data[$param][$name];
        }               
      }
      $config['all']['rss'] = $this->settings;
      file_put_contents(sfConfig::get('sf_apps_dir') . '/frontend/config/app.yml', sfYaml::dump($config, 4));
      $this->flushAppConfig();      
      $this->getUser()->setFlash('notice', 'Успешно сохранено');
      $this->redirect('feed/index');
    }
  }

  protected function flushAppConfig($name = 'config_app.yml.php')
  {
    $fs = new sfFilesystem();
    $fs->remove(sfFinder::type('file')->name($name)->in(sfConfig::get('sf_cache_dir')));
  }
}
