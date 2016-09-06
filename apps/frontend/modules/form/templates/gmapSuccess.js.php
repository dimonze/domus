<?php if ($region && $region->latitude && $region->longitude && $region->zoom): ?>
  window.gmap_center = [<?= $region->latitude ?>, <?= $region->longitude ?>];
  window.gmap_zoom   = <?= $region->zoom ?>;
<?php endif ?>
