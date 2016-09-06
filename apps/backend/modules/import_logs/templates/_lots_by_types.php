<?php $lots = 0 ?>
<?php foreach ($import_log->Types as $type): ?>
  <span>
    <?php $lot_type = array_search($type->type, Lot::$types)?>
    <?= Lot::$type_ru[$lot_type] ?>&nbsp;-&nbsp;<?= $type->lots ?>
  </span><br />
  <?php $lots = $lots + $type->lots ?>
<?php endforeach ?>
<span><b>Всего:</b>&nbsp;<?= $lots ?></span>