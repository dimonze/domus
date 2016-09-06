<div class="adv-line adv-line-view-2 search-ads" style="display:none;"><!-- если убрать класс adv-line-view-2 то будет другой вид - с рамкой -->
  <div class="frame-wrap">
    <?php if (isset($place) && $sf_request->isXmlHttpRequest()) : ?>
      <div class="mesto-banner" id="mesto-zone_74-<?= $place ?>"></div>
    <?php endif; ?>
    <script type='text/javascript'>
      <!--// <![CDATA[
        mesto_bind(74);
      // ]]> -->
    </script>
  </div>
  <div class="shadow"></div>
  <div class="r"></div>
</div>