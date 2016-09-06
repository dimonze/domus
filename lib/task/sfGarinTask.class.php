<?php

abstract class sfGarinTask extends sfBaseTask {
  
  public function writeProgress($text = '') {
    static
      $bar = array('|', '/', '-', '\\'),
      $prev = -1,
      $prev_text = '';

    if (empty($text)) $text = $prev_text;
    else $prev_text = $text;

    $prev = ++$prev >= count($bar) ? 0 : $prev;
    echo $bar[$prev].' '.$text."\r";
  }
}