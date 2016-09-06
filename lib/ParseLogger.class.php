<?php

class ParseLogger
{
  const
    EMPTY_PARAMS    = 'empty parameters',
    EMPTY_GEODATA   = 'empty geodata',
    EMPTY_ADDRESS   = 'empty address',
    EMPTY_ADDRESS2  = 'empty street address',
    EMPTY_GADDRESS  = 'empty geodata address',
    EMPTY_GADDRESS2 = 'empty geodata street address',
    EMPTY_PHONE     = 'empty phone number',
    EMPTY_PRICE     = 'no price specified or < 0',
    EMPTY_AREA      = 'no area specified or < 0',
    BAD_AREA        = 'area is too little',
    BAD_ADDRESS     = 'invalid address',
    BAD_STATUS      = 'lot hasn\'t got active status',
    MATCH_ERROR     = 'can\'t determine region',
    PRICE_ERROR     = 'price is out of range',
    OCR_ERROR       = 'can\'t recognize text',
    REGION_ERROR    = 'wrong region detected',
    REGION_UNKNOWN  = 'can\'t find region',
    ROOMS_NUM       = 'unspecified number of rooms',
    COMM_TYPE       = 'unspecified commercial type',
    EXCEPTION       = 'exception thrown',
    EXISTS          = 'lot already exists',
    NEWER_EXISTS    = 'newer lot is exists',
    SIMILAR         = 'similar lot deactivated';
  
  private static
    $log_file     = '/parsing.log',
    $file_stream  = null,
    $separator1   = '',
    $separator2   = "\r\n",
    $counter      = 0;

  public static function initLogger(array $options)
  {
    self::$log_file = sprintf('%s/parsing-%s.log', sfConfig::get('sf_log_dir'), date('Y-m-d'));
    if (!$f = fopen(self::$log_file, 'a')) {
      throw new Exception('Can\'t create or open file for writing: '.self::$log_file);
    }
    self::$file_stream = $f;

    if (self::$separator1 == '') {
      for ($i=0, $max=60; $i<=$max; $i++) {
        self::$separator1 .= '=';
        self::$separator2 .= '-';
        if ($i == $max)
          self::$separator1 .= "\r\n";
      }
    }

    $text = self::$separator1;
    $text .= date('Y-m-d H:i:s').' - Start parsing '.$options['resource'].': '.$options['type'].'. Limit: '.$options['limit'].' lots';
    $text .= self::$separator2."\r\n".$options['page'];
    self::write($text);
  }

  public static function writeError($url, $message, $info = null) {
    self::$counter++;
    $text = self::$counter.' - '.$url.' --- '.$message;
    if (!empty($info)) $text .= ' ('.$info.')';
    self::write($text);
  }

  public static function writeInfo($url, $message, $info) {
    self::write('-  - '.$url.' --- '.$message.' ('.$info.')');
  }

  public static function writeStart($lots) {
    $text = 'Found '.$lots.' lots'.self::$separator2;
    self::write($text);
  }

  public static function writeFinish($parsed, $fetched) {
    $text = self::$separator2."\r\n".date('Y-m-d H:i:s').' - Parsed '.$parsed.' of '.$fetched.' lots. Errors: '.self::$counter;
    self::write($text);
    fclose(self::$file_stream);
    self::$counter = 0;
  }

  private static function write($text) {
    if (!fwrite(self::$file_stream , $text."\r\n")) throw new Exception('Can\'t write to log file '.self::$log_file);
  }

}