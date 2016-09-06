<?php $zones =  array(
    235, 236, 237, 238, 239, 240, 241, 286, 287, 288, 290, 297, 298, 299, 300, 301, 302, 303, 304, 305,
    306, 307, 309, 310, 311, 312, 313, 314, 315, 316, 317, 318, 319, 320, 323, 324, 325, 326, 327, 328
  );
?>
<?php foreach ($zones as $zone): ?>
  <div class="searchResultItem buildingView searchResultItem2 spec-lot">
    <div class="padding_6">
      <script type='text/javascript'><!--// <![CDATA[
        mesto_bind(<?= $zone ?>);
      // ]]> -->
      </script>
    </div>
  </div>
<?php endforeach; ?>

<?php for ($i=1; $i <=6; $i++): ?>
  <div class="searchResultItem buildingView <? if($i <= 3) echo "buildingView_yellow" ?> searchResultItem2 spec-lot">
    <div class="padding_6">
        <div class="mesto-banner" id="mesto-zone_74-<?= $i ?>"></div>
    </div>
  </div>
<?php endfor ?>
