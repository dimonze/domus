<?php

/**
 * price actions.
 *
 * @package    domus
 * @subpackage price
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class priceActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $file = sfConfig::get('sf_config_dir') . '/import_prices.yml';
    $prices = sfYaml::load($file);

    if ($request->isMethod('post') && $request->hasParameter('prices')) {
      $data = $request->getParameter('prices');
      foreach($prices['all'] as $type => $value) {
        $prices['all'][$type] = isset($data[$type]) ? $data[$type]: $prices['all'][$type];
      }

      file_put_contents($file, sfYaml::dump($prices));


      $fs = new sfFilesystem();
      $fs->remove(sfFinder::type('file')
         ->name('config_import_prices.yml.php')
         ->in(sfConfig::get('sf_cache_dir')));

      $this->getUser()->setFlash('notice', 'Успешно сохранено');
      $this->redirect('price/index');
    }
    else {
      file_put_contents($file, sfYaml::dump($prices));
    }

    $this->prices = $prices['all'];
  }
}
