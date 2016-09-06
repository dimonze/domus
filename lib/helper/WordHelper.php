<?php

function ending ($num, $ending1, $ending2, $ending5 = null, $ending78 = null){
  if (!$ending5) {
    $ending5 = $ending2;
  }

  $len = strlen($num);
  $last =  substr($num, -1);
  if ($len > 1) {
    $prev =  substr($num, -2, 1);
  }

  if ($num > 6 && $num < 9 && $ending78) {
    $result = $ending78;
  }
  elseif ($last == 1){
    if ($len > 1){
      $result = $prev == 1 ? $ending5 : $ending1;
    }
    else{
      $result = $ending1;
    }
  }
  elseif($last > 1 && $last < 5){
    if ($len > 1){
      $result = $prev == 1 ? $ending5 : $ending2;
    }
    else{
      $result = $ending2;
    }
  }
  else{
    $result = $ending5;
  }
  return $result;
}

function feed_escaping ($string = null)
{
  if (null != $string){
    $string = preg_replace('/<img.*\/>/iUs', '', $string);
    $search = array('&', '<', '>', '\'', '"');
    $replace = array('&amp;', '&lt;', '&gt;', '', '&apos;', '&quot;');
    return str_replace($search, $replace, $string);
  }
}