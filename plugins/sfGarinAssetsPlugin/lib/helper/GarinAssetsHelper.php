<?php
/**
 * Prints <script> tags for all javascripts configured in view.yml or added to the response object.
 * and appends files modification time to query string
 *
 * @see include_javascripts()
 */
function include_javascripts_qs()
{
  $response = sfContext::getInstance()->getResponse();
  sfConfig::set('symfony.asset.javascripts_included', true);
  $host = sfConfig::get('app_static_host');

  $files = array();
  foreach ($response->getJavascripts() as $file => $options) {
    $files[javascript_path($file, false)] = $options;
  }

  foreach ($files as $file => $options) {
    if (!strpos($file, '://') && file_exists($path = sfConfig::get('sf_web_dir') . $file)) {
      $time = filemtime($path);
    }
    else {
      $time = null;
    }
    $file = javascript_path($file . ($time ? '?' . $time : ''), !empty($options['absolute']));

    if ($host && !strpos($file, '://')) {
      $file = sprintf('//%s%s', $host, $file);
    }

    echo javascript_include_tag($file, $options);
  }
}

/**
 * Prints <link> tags for all stylesheets configured in view.yml or added to the response object.
 * and appends files modification time to query string
 *
 * @see include_stylesheets()
 */
function include_stylesheets_qs()
{
  $response = sfContext::getInstance()->getResponse();
  sfConfig::set('symfony.asset.stylesheets_included', true);
  $host = sfConfig::get('app_static_host');

  $files = array();
  foreach ($response->getStylesheets() as $file => $options) {
    $files[stylesheet_path($file, false)] = $options;
  }

  foreach ($files as $file => $options) {
    if (!strpos($file, '://') && file_exists($path = sfConfig::get('sf_web_dir') . $file)) {
      $time = filemtime($path);
    }
    else {
      $time = null;
    }
    $file = stylesheet_path($file . ($time ? '?' . $time : ''), !empty($options['absolute']));


    if ($host && !strpos($file, '://')) {
      $file = sprintf('//%s%s', $host, $file);
    }

    echo stylesheet_tag($file, $options);
  }
}

function image_tag_s($source, $options = array())
{
  if (!$source) {
    return '';
  }

  if ($host = sfConfig::get('app_static_host')) {
    $source = sprintf('//%s%s', $host, image_path($source, false));
  }

  return image_tag($source, $options);
}