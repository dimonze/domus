<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Clears the symfony cache.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfCacheClearTask.class.php 12548 2008-11-01 16:55:27Z fabien $
 */
class sfThumbnailCacheClearTask extends sfBaseTask
{
  protected
    $config = null;

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('path', null, sfCommandOption::PARAMETER_OPTIONAL, 'Path', null),
      new sfCommandOption('basedir', null, sfCommandOption::PARAMETER_OPTIONAL, 'sfCachedThumbnail::$baseDir', null)
    ));

    $this->namespace = 'thumbnail';
    $this->name = 'clear';
    $this->briefDescription = 'Clears the thumnail cache';
    $this->detailedDescription = '';
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    require_once(sfConfig::get('sf_plugins_dir').'/sfThumbnailPlugin/lib/sfCachedThumbnail.class.php');
    
    sfCachedThumbnail::setBaseDir($options['basedir']);
    $dirs = sfFinder::type('dir')->in(sfCachedThumbnail::getBaseDir($options['path']));
    foreach ($dirs as $dir) {
      if (file_exists($dir.'/.cache')) {
        $this->getFilesystem()->remove(glob($dir.'/*'));
        $this->getFilesystem()->remove($dir.'/.cache');
        $this->getFilesystem()->remove($dir);
      }
    }
  }

}
