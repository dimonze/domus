<style type="text/css">
  .index-adv-teaser{
    font-size:12px;
  }
  .index-adv-teaser.vertical{
    width:300px;
  }
  .index-adv-teaser.horizontal{
    width:1000px;
  }
  .index-adv-teaser > .wrap{
    overflow:hidden;
    text-align:left;
  }
  .index-adv-teaser > .wrap > div{
    min-height:100px;
    margin-bottom:15px;
  }
  .index-adv-teaser.horizontal > .wrap > div {
    margin-right:30px;
    margin-bottom:0;
    width:300px;
    display:inline-block;
    vertical-align:top;
    height:100px;
    overflow:hidden;
  }
  .index-adv-teaser > .wrap > div img{
    display:block;
    float:left;
    padding-right:10px;
    height:100px;
    width:auto;
  }
  .index-adv-teaser > .wrap > div b{
    font-size:12px;
    margin:0;
    color:#0278C2;
  }
  .index-adv-teaser > .wrap > div a{
    font-family: Arial, sans-serif;
    color:#0278C2;
  }
  .index-adv-teaser > .wrap > div span{
    font-family: Arial, sans-serif;
    color:#595959;
    margin:5px 0 0 0;
    display:block;
    overflow:hidden;
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

<!-- Yandex.Metrika counter -->
<script type="text/javascript">
(function (d, w, c) {
    (w[c] = w[c] || []).push(function() {
        try {
            w.yaCounter23415622 = new Ya.Metrika({id:23415622,
                    webvisor:true,
                    clickmap:true,
                    trackLinks:true,
                    accurateTrackBounce:true});
        } catch(e) { }
    });

    var n = d.getElementsByTagName("script")[0],
        s = d.createElement("script"),
        f = function () { n.parentNode.insertBefore(s, n); };
    s.type = "text/javascript";
    s.async = true;
    s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";

    if (w.opera == "[object Opera]") {
        d.addEventListener("DOMContentLoaded", f, false);
    } else { f(); }
})(document, window, "yandex_metrika_callbacks");
</script>
<noscript><div><img src="//mc.yandex.ru/watch/23415622" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
