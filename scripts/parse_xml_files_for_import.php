<?php
  $file = '../cache/import/107';

  $dom = new DOMDocument('1.0', 'UTF-8');
  $dom->load($file, LIBXML_NOBLANKS |  LIBXML_NOCDATA);
  $nodes = $dom->getElementsByTagName('offer');
  $begin = '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL .
    '<realty-feed>' . PHP_EOL;

  $fh = fopen('../cache/107-new.xml', 'a');
  fwrite($fh, $begin);
  for ($i=0; $i<$nodes->length; $i++) {
    $item = $dom->saveXML($nodes->item($i));
    fwrite($fh, $item . PHP_EOL);
  }
  fwrite($fh, PHP_EOL . '</realty-feed>');
  fclose($fh);
?>