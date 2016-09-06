<?php

/*
 * (c) Eugeniy Belyaev <eugeiy.b@garin-studio.ru>
 */

/**
 * sfCachedThumbnail provides a mechanism for creating thumbnail images.
 * @see http://www.php.net/gd
 *
 * @package    sfThumbnailPlugin
 * @author     Eugeniy Belyaev <eugeiy.b@garin-studio.ru>
 */

abstract class sfCachedThumbnail {

  static $baseDir = null;
  static $webDir = null;

  public static function setBaseDir($dir = null) {
    self::$baseDir = is_null($dir) ? null : preg_replace('~/$~', '', $dir);
  }

  public static function getBaseDir($path = null) {
    if (self::$baseDir === null) {
      self::$baseDir = sfConfig::get('sf_web_dir');
    }

    return self::$baseDir . ($path ? '/'.$path : '');
  }

  public static function setWebDir($dir = null) {
    self::$webDir = is_null($dir) ? null : preg_replace('~/$~', '', $dir);
  }

  public static function getWebDir($path = null) {
    if (self::$webDir === null) {
      self::$webDir =  str_replace(sfConfig::get('sf_web_dir'), '', self::getBaseDir());
    }

    return self::$webDir . ($path ? '/'.$path : '');
  }


  public static function getImage($path, $file, $width = 0, $height = 0, $scale = true, $inflate = true, $quality = 80){
    if($width <= 0 && $height <= 0) {
      throw new Exception('sfCachedThumbnail: width or height must be specified');
    }

    $source = self::getBaseDir($path.'/source/'.$file);
    $thumb = self::getBaseDir($path.'/'.$width.'x'.$height.'/'.$file);
    $thumb_web = self::getWebDir($path.'/'.$width.'x'.$height.'/'.$file);

    if(!file_exists($source)) {
      return null;
    }
    if (file_exists($thumb) && filemtime($thumb) > filemtime($source)) {
      return $thumb_web;
    }
    if (!is_dir(dirname($thumb))) {
      mkdir(dirname($thumb));
      touch(dirname($thumb).'/.cache');
      chmod(dirname($thumb), 0777);
      chmod(dirname($thumb).'/.cache', 0666);
    }

    $thumbnail = new sfThumbnail($width, $height, $scale, $inflate, $quality);
    $thumbnail->loadFile($source);
    $thumbnail->save($thumb);
    chmod($thumb, 0666);

    return $thumb_web . '?' . filemtime($thumb);
  }

  public static function removeImage($path, $file) {
    $dir_h = opendir(self::getBaseDir($path));
    while (false !== ($node = readdir($dir_h))) {
      $filename = self::getBaseDir($path.'/'.$node.'/'.$file);
      if (file_exists(self::getBaseDir($path.'/'.$node.'/.cache')) && file_exists($filename)) {
        @unlink($filename);
      }
    }
  }

}
