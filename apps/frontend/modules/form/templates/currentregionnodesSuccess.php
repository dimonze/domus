<?php if (!empty($nodes)): ?>
  <?php if (in_array($region_id, array(77,78))): ?>
    <?php include_partial('map_and_metro_box', array('nodes' => $nodes, 'stage' => $stage))?>
  <?php else: ?>
    <?php include_partial('region_nodes', array('nodes' => $nodes, 'shosse' => $shosse, 'rayon' => $rayon, 'stage' => $stage))?>
  <?php endif ?>
<?php endif ?>
