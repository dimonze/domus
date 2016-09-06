<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2007 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfThumbnail provides a mechanism for creating thumbnail images.
 *
 * This is taken from Harry Fueck's Thumbnail class and
 * converted for PHP5 strict compliance for use with symfony.
 *
 * @package    sfThumbnailPlugin
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Benjamin Meynell <bmeynell@colorado.edu>
 */
class sfThumbnail
{
  /**
   * Width of source in pixels
   */
  protected $sourceWidth;

  /**
   * Height of source in pixels
   */
  protected $sourceHeight;

  /**
   * Width of thumbnail in pixels
   */
  protected $thumbWidth;

  /**
   * Height of thumbnail in pixels
   */
  protected $thumbHeight;

  /**
   * Temporary file if the source is not local
   */
  protected $tempFile = null;

  protected $adapters = array('sfIMagickAdapter', 'sfGDAdapter');

  /**
   * @var sfGDAdapter
   */
  protected $adapter = null;

  /**
   * Thumbnail constructor
   *
   * @param int (optional) max width of thumbnail
   * @param int (optional) max height of thumbnail
   * @param boolean (optional) if true image scales
   * @param boolean (optional) if true inflate small images
   * @param string (optional) adapter class name
   * @param array (optional) adapter options
   */
  public function __construct($maxWidth = null, $maxHeight = null, $scale = true, $inflate = true, $quality = 80, $adapterClass = null, $adapterOptions = array())
  {    
    if (!$adapterClass)
    {
      foreach ($this->adapters as $adapterClass) {
        try {
          $this->adapter = new $adapterClass($maxWidth, $maxHeight, $scale, $inflate, $quality, $adapterOptions);
          break;
        } catch (Exception $e) {
          continue;
        }
      }
    }
    else {
      $this->adapter = new $adapterClass($maxWidth, $maxHeight, $scale, $inflate, $quality, $adapterOptions);
    }
    
    if (!$this->adapter) {
      throw new Exception('No image adapters available');
    }
  }

  /**
   * @return sfGDAdapter
   */
  public function getAdapter()
  {
    return $this->adapter;
  }

  public static function create($maxWidth = null, $maxHeight = null, $scale = true,
                                $inflate = true, $quality = 80, $adapterClass = null,
                                $adapterOptions = array())
  {
    return new self($maxWidth, $maxHeight, $scale, $inflate, $quality, $adapterClass, $adapterOptions);
  }

  /**
   * Loads an image from a file and creates an internal thumbnail out of it
   *
   * @param string filename (with absolute path) of the image to load
   *
   * @return boolean True if the image was properly loaded
   * @throws Exception If the image cannot be loaded, or if its mime type is not supported
   */
  public function loadFile($image, $crop = array())
  {
    if (preg_match('~^https?://~', $image))
    {
      if (class_exists('sfWebBrowser'))
      {
        if (!is_null($this->tempFile)) {
          unlink($this->tempFile);
        }
        $this->tempFile = tempnam('/tmp', 'sfThumbnailPlugin');

        $b = new sfWebBrowser();
        try
        {
          $b->get($image);
          if ($b->getResponseCode() != 200) {
            throw new Exception(sprintf('%s returned error code %s', $image, $b->getResponseCode()));
          }
          file_put_contents($this->tempFile, $b->getResponseText());
          if (!filesize($this->tempFile)) {
            throw new Exception('downloaded file is empty');
          } else {
            $image = $this->tempFile;
          }
        }
        catch (Exception $e)
        {
          throw new Exception("Source image is a URL but it cannot be used because ". $e->getMessage());
        }
      }
      else
      {
        throw new Exception("Source image is a URL but sfWebBrowserPlugin is not installed");
      }
    }    
    list($this->sourceWidth, $this->sourceHeight) = $this->adapter->loadFile($this, $image, $crop);
    return $this;
  }

  /**
  * Loads an image from a string (e.g. database) and creates an internal thumbnail out of it
  *
  * @param string the image string (must be a format accepted by imagecreatefromstring())
  * @param string mime type of the image
  *
  * @return boolean True if the image was properly loaded
  * @access public
  * @throws Exception If image mime type is not supported
  */
  public function loadData($image, $mime)
  {
    list($this->sourceWidth, $this->sourceHeight) = $this->adapter->loadData($this, $image, $mime);
    return $this;
  }

  /**
   * Saves the thumbnail to the filesystem
   * If no target mime type is specified, the thumbnail is created with the same mime type as the source file.
   *
   * @param string the image thumbnail file destination (with absolute path)
   * @param string The mime-type of the thumbnail (possible values are 'image/jpeg', 'image/png', and 'image/gif')
   *
   * @access public
   * @return void
   */
  public function save($thumbDest, $targetMime = null)
  {
    $this->adapter->save($this, $thumbDest, $targetMime);
  }

  /**
   * Returns the thumbnail as a string
   * If no target mime type is specified, the thumbnail is created with the same mime type as the source file.
   *
   *
   * @param string The mime-type of the thumbnail (possible values are adapter dependent)
   *
   * @access public
   * @return string
   */
  public function toString($targetMime = null)
  {
    return $this->adapter->toString($this, $targetMime);
  }

  public function freeSource()
  {
    if (!is_null($this->tempFile)) {
      unlink($this->tempFile);
    }
    $this->adapter->freeSource();
  }

  public function freeThumb()
  {
    $this->adapter->freeThumb();
  }

  public function freeAll()
  {
    $this->adapter->freeSource();
    $this->adapter->freeThumb();
  }

  /**
   * Returns the width of the thumbnail
   */
  public function getThumbWidth()
  {
    return $this->thumbWidth;
  }

  /**
   * Returns the height of the thumbnail
   */
  public function getThumbHeight()
  {
    return $this->thumbHeight;
  }

  /**
   * Returns the width of the source
   */
  public function getSourceWidth()
  {
    return $this->sourceWidth;
  }

  /**
   * Returns the height of the source
   */
  public function getSourceHeight()
  {
    return $this->sourceHeight;
  }

  /**
   * Returns the mime type of the source image
   */
  public function getMime()
  {
    return $this->adapter->getSourceMime();
  }

  /**
   * Computes the thumbnail width and height
   * Used by adapter
   */
  public function initThumb($sourceWidth, $sourceHeight, $maxWidth, $maxHeight, $scale, $inflate)
  {    
    if ($maxWidth > 0)
    {
      $ratioWidth = $maxWidth / $sourceWidth;
    }
    if ($maxHeight > 0)
    {
      $ratioHeight = $maxHeight / $sourceHeight;
    }

    if ($scale)
    {
      if ($maxWidth and $maxHeight)
      {
        $ratio = min($ratioWidth, $ratioHeight);
      }
      if ($maxWidth xor $maxHeight)
      {
        $ratio = (isset($ratioWidth)) ? $ratioWidth : $ratioHeight;
      }
      if ((!$maxWidth && !$maxHeight) or (!$inflate && $ratio > 1))
      {
        $ratio = 1;
      }

      $this->thumbWidth = floor($ratio * $sourceWidth);
      $this->thumbHeight = floor($ratio * $sourceHeight);
    }
    else
    {
      if (!isset($ratioWidth) || (!$inflate && $ratioWidth > 1))
      {
        $ratioWidth = 1;
      }
      if (!isset($ratioHeight) || (!$inflate && $ratioHeight > 1))
      {
        $ratioHeight = 1;
      }
      $this->thumbWidth = floor($ratioWidth * $sourceWidth);
      $this->thumbHeight = ceil($ratioHeight * $sourceHeight);
    }

    if ($this->thumbWidth < 1 || $this->thumbHeight < 1) {
      throw new Exception('Zero thumbmail size');
    }
  }

  public function __destruct()
  {
    $this->freeAll();
  }

  public function getCrop($crop, $width, $height)
  {
    if ('square' == $crop) {
      if ($width > $height) {
        return array(
          'x' => round(($width - $height) / 2),
          'y' => 0,
          'width'  => $height,
          'height' => $height,
        );
      }
      elseif ($width < $height) {
        return array(
          'x' => 0,
          'y' => round(($height - $width) / 2),
          'width'  => $width,
          'height' => $width,
        );
      }
    }
    
    elseif (is_array($crop) && isset($crop['x'], $crop['y'], $crop['width'], $crop['height'])) {
      if ($crop['x'] >= 0 && $crop['y'] >= 0 && $crop['width'] > 0 && $crop['height'] > 0) {
        return $crop;
      }
    }

    return null;
  }
}
