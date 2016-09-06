<script type="text/javascript">
  var OA_zones = {
    <?php for($i=1; $i <= 3; $i++): ?>
      <?= "'zone_" . $zone_id . "-" . $i . (($i != $zones ) ? "' : 330," : "' : 330") . PHP_EOL ?>

    <?php endfor ?>
  };
</script>
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/js/banners.js"></script>
<link rel="stylesheet" type="text/css" media="all" href="/css/main.css">

<?php for($i=1; $i <= 3; $i++): ?>
  <div class="searchResultItem buildingView buildingView_yellow searchResultItem2 spec-lot">
    <div class="padding_6">
      <?php $zone = $zone_id . '-' . $i ?>
      <?php $cb = md5($zone . rand(0, 1000))?>
      <div class="mesto-banner" id="mesto-zone_<?= $zone ?>"></div>
    </div>
  </div>
<?php endfor ?>

<?php include_partial('banner/lazy_openx') ?>