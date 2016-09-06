<?php if ('Search' == $notification->model): ?>
  <?= $notification->Search->text ?>

<?php else: ?>
  <?= $notification->field ?>
  id: <?= $notification->pk ?>
<?php endif ?>
