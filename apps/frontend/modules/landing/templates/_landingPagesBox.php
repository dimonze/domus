<?php if (!empty($lpages)): ?>
  <div class="articles-analytics-box articles-analytics-box-posts lpages_wrap">
    <div class="articles-analytics-box_wrap">
      <div class="wrap">
        <?php foreach($lpages as $lpage): ?>
          <p>
            <?= link_to($lpage['attrs']['h1'], Toolkit::getGeoHostByRegionId($lpage['attrs']['region_id'], false) . '/' . $lpage['attrs']['url']) ?>
          </p>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="articles-analytics-box_b"></div>  
<?php endif ?>