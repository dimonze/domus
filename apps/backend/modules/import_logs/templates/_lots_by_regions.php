<?php $lots = 0 ?>
<?php foreach ($import_log->Regions as $region): ?>
  <span>
    <?= $region->Region->name ?>&nbsp;-&nbsp;<?= $region->lots ?>
  </span><br />
  <?php $lots = $lots + $region->lots ?>
<?php endforeach ?>
<span><b>Всего:</b>&nbsp;<?= $lots ?></span>