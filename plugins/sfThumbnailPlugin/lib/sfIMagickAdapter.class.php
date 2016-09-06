<?php

/*
 * (c) Eugeniy Belyaev <eugeiy.b@garin-studio.ru>
 */

/**
 * sfIMagickAdapter provides a mechanism for creating thumbnail images.
 * @see http://www.php.net/gd
 *
 * @package    sfThumbnailPlugin
 * @author     Eugeniy Belyaev <eugeiy.b@garin-studio.ru>
 */

class sfIMagickAdapter
{

  protected
    $sourceWidth,
    $sourceHeight,
    $sourceMime,
    $maxWidth,
    $maxHeight,
    $scale,
    $inflate,
    $quality,
    $source,
    $thumb;



  public function __construct($maxWidth, $maxHeight, $scale, $inflate, $quality, $options)
  {
    if (!extension_loaded('imagick') || !class_exists('Imagick'))
    {
      throw new Exception ('imagick not enabled. Check your php.ini file.');
    }
    $this->maxWidth = $maxWidth;
    $this->maxHeight = $maxHeight;
    $this->scale = $scale;
    $this->inflate = $inflate;
    $this->quality = $quality;
    $this->options = $options;
  }

  protected function load(sfThumbnail $thumbnail) {
    $img_size = array('columns' => $this->source->getImageWidth(), 'rows' => $this->source->getImageHeight());
    
    $this->sourceWidth = $img_size['columns'];
    $this->sourceHeight = $img_size['rows'];

    $thumbnail->initThumb($this->sourceWidth, $this->sourceHeight, $this->maxWidth, $this->maxHeight, $this->scale, $this->inflate);

    $this->thumb = $this->source->clone();

    if ($img_size['columns'] != $this->maxWidth or $img_size['rows'] != $this->maxHeight)
    {
      $this->thumb->thumbnailImage($thumbnail->getThumbWidth(), $thumbnail->getThumbHeight());
    }

    return array($this->sourceWidth, $this->sourceHeight);
  }

  protected function crop(sfThumbnail $thumbnail, $data)
  {
    if ($crop = $thumbnail->getCrop($data, $this->source->getImageWidth(), $this->source->getImageHeight())) {
      $this->source->cropImage($crop['width'], $crop['height'], $crop['x'], $crop['y']);
      $this->source->setImagePage(0, 0, 0, 0);
    }
  }

  public function loadFile(sfThumbnail $thumbnail, $image, $crop = null)
  {
    $this->source = new Imagick($image);
    $this->crop($thumbnail, $crop);
    return $this->load($thumbnail);
  }

  public function loadData(sfThumbnail $thumbnail, $image, $mime, $crop = null)
  {
    $this->source = new Imagick();
    $this->source->readimageblob($image);
    $this->crop($thumbnail, $crop);
    return $this->load($thumbnail);
  }

  public function save($thumbnail, $thumbDest, $targetMime = null)
  {
    if($targetMime !== null)
    {
      $this->thumb->setImageFormat(str_replace('image/', '', $targetMime));
    }

    if (strtolower($this->thumb->getImageFormat()) == 'jpeg')
    {
      $this->thumb->setCompressionQuality($this->quality);
    }
    
    return $this->thumb->writeImage($thumbDest);
  }

  public function toString($thumbnail, $targetMime = null)
  {
    if($targetMime !== null)
    {
      $this->thumb->setImageFormat(str_replace('image/', '', $targetMime));
    }

    return (string) $this->thumb;
  }

  public function freeSource()
  {
    return $this->source && $this->source->destroy();
  }

  public function freeThumb()
  {
    return $this->thumb && $this->thumb->destroy();
  }

  public function getSourceMime()
  {
    return 'image/' . strtolower($this->source->getImageFormat());
  }

}
