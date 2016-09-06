<style type="text/css">
  .index-adv-teaser {
    margin: 0 0 1em;
    font-size: 0.846em;
  }
  .index-adv-teaser .wrap {
    min-height: 225px;
    text-align: left;
    padding: 10px 0 20px 6px;
    margin: 0 0 0.8em;
    overflow: hidden;
    zoom: 1;
  }
  
  .index-adv-teaser .wrap div {
    width: 146px;
    float: left;
    margin: 10px 7px;
    color: black;
    line-height: 1.6em;
    min-height: 270px;
    *height: 270px;
  }
  .index-adv-teaser .wrap div span {
    line-height: 1.4;
    display: block;
    margin: 3px 0 0 0;
  }
  
  .vertical {
    width: 300px;
    float: left;
    position: relative;
    margin: 10px 0;
    font-size: 0.846em;
    padding: 0 12px;
  }
  .vertical .wrap div {
    line-height: 1.6em;
    margin: 0 0 11px;
    padding: 5px 0;
    zoom: 1;
    overflow: hidden;
    min-height: 100px;
    *height: 100px;
    clear: both;
  }
</style>
<div class="index-adv-teaser <?= $align ?>">
  <div class="wrap">
    <script type="text/javascript">
      var OA_zones = {
        <?php for($i=1; $i <= $zones; $i++): ?>
          <?= "'zone_" . $zone_id . "-" . $i . (($i != $zones ) ? "' : 330," : "' : 330") . PHP_EOL ?>

        <?php endfor ?>
      };
    </script>
    <script type="text/javascript" src="/js/jquery.js"></script>
    <script type="text/javascript" src="/js/banners.js"></script>

    <?php for($i=1; $i <= $zones; $i++): ?>
      <?php $zone = $zone_id . '-' . $i ?>
      <?php $cb = md5($zone . rand(0, 1000))?>
      <div class="mesto-banner" id="mesto-zone_<?= $zone ?>"></div>
    <?php endfor ?>

    <?php include_partial('banner/lazy_openx') ?>
  </div>
</div>
