<?php

/**
 * Toolkit
 *
 * @author     Garin Studio
 */
abstract class Currency
{
  public static $currencies = array(
    'RUR' =>  1,
    'USD' =>  2,
    'EUR' =>  3
  );
  private static $rates = array();

  private static function checkCurrency($currency) {
    if (!is_array($currency)) {
      $currency = array($currency);
    }
    $rates = self::getRates();
    foreach ($currency as $c) {
      if (!isset($rates[$c])) {
        throw new sfException("Currency $c not supported yet");
      }
    }
  }

  public static function getRates() {
    if (!count(self::$rates)) {
      $rates = sfConfig::get('app_exchange_rates', array());
      foreach ($rates as $currency => $rate) {
        foreach ($rates as $s_currency => $s_rate) {
          self::$rates[$currency][$s_currency] = $rate / $s_rate;
        }
      }
    }
    return self::$rates;
  }
  
  public static function convert($amount, $from, $to) {
    if ($from == $to) {
      return $amount;
    }
    else {
      self::checkCurrency(array($from, $to));
      return round($amount * self::$rates[$from][$to], 4);
    }
  }

  public static function formatPrice($amount, $currency, $convert = null) {
    $currency = strtoupper($currency);
    if ($convert) {
      $amount = self::convert($amount, $currency, $convert);
      $currency = $convert;
    }
    
    if ($currency == 'USD')      $sign_before = '$';
    elseif ($currency == 'EUR')  $sign_before = '&euro;';
    else                         $sign_after  = '&nbsp;руб.';

    $amount = str_split((string) round($amount));
    $value = '';
    $c = 0;
    for ($i = count($amount) - 1; $i >= 0; $i--) {
      $value = $amount[$i] . ($c++ % 3 ? '' : '&nbsp;') . $value;
    }

    if (isset($sign_before)) {
      return $sign_before . $value;
    }
    elseif (isset($sign_after)) {
      return $value . $sign_after;
    }
    else {
      return $value;
    }
  }
}
