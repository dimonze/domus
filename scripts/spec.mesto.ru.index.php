<?php
  if (empty($_GET['specials'])) {
    exit();
  }
  $specials = (int) $_GET['specials'];
  $align    = $_GET['align'];
  $target   = $_GET['target'];
?>
<html>
  <head>
    <meta name="robots" content="noindex">
    <META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
  </head>
  <body>
    <iframe id='specials_frame' name='specials_frame' src='http://www.mesto.ru/lot/specials/<?= $specials . (!empty($align) ? "?align=" . $align : '')  . (!empty($target) ? "?target=" .  $target : '') ?>' frameborder='0' scrolling='<?= (!empty($target) ? 'no' : 'yes')?>' width='100%' height='<?= (!empty($target) ? '430px' : '100%') ?>'></iframe>
  </body>
</html>