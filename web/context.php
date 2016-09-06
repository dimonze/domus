<?php
if (!$_GET['url']) {
        exit();
}
$cval = strstr($_GET['url'], 'dmir') ? 0.35 : 0;
$url = substr($_SERVER['QUERY_STRING'], preg_match('!url=!', $_SERVER['QUERY_STRING']) ? strpos($_SERVER['QUERY_STRING'], "url=")+4 : 0);

// #8892
if (!strpos($url, '?') && strpos($url, '&_openstat=')) {
  $url = str_replace('&_openstat=', '?_openstat=', $url);
}
// \

$parts = parse_url($url);

if ($_SERVER['HTTP_HOST'] ==  $parts['host']) {
  if (!strstr($_GET['url'], 'context')) {
    $url = $_GET['url'];
  }
  else {
    $url = "http://".$parts['host'];
  }
}
?>
<html>
  <head>
    <meta name="robots" content="noindex">
    <META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
    <noscript>
      <META HTTP-EQUIV='refresh' content='1; url=<?php echo $url?>'>
    </noscript>
  </head>
  <body>
    <script type='text/javascript'>setTimeout('window.location.href=\'<?php echo $url?>\'', 1000);</script>
  </body>
</html>