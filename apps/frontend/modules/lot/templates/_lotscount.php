<?php $type = array('объявление', 'объявления', 'объявлений') ?>
<?php $lots_string = ending($lots_nb, $type[0], $type[1], $type[2]) ?>
<div>
  <?php if (isset($lots_nb)): ?>
    <?php $lots_nb = preg_replace('/(\d+)(\d{3})$/', '\\1&nbsp;\\2', $lots_nb) ?>
    <?= $counter = preg_replace('/(\d)/', '<span class="num\\1"></span>', $lots_nb)?>
  <?php endif ?>
</div>
<?= $lots_string ?>