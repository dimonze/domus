<?php if ($only_count): ?>
  <?php if (isset($lots_nb)): ?>
    <?= $lots_nb ?>
  <?php endif ?>
<?php else: ?>
  <?php $type = array('объявление', 'объявления', 'объявлений') ?>
  <?php $lots_string = ending($lots_nb, $type[0], $type[1], $type[2]) ?>
  <div>
    <?php if (isset($lots_nb)): ?>
      <?= $counter = preg_replace('/(\d)/', '<span class="num\\1"></span>', $lots_nb)?>
    <?php endif ?>
  </div>
  <?= $lots_string ?>
<?php endif ?>