<span>Объявлений: <?= $import_log->lots ?></span><br />
<span class="import_green">Загружено: <?php $s=0; foreach ($import_log->Types as $t) $s += $t->lots; echo $s; ?></span><br />
<?php if ($import_log->errors > 0): ?>
  <span class="import_red">Ошибок: <?= $import_log->errors ?> !</span><br />
  <a href="<?= url_for('@import_log_errors?id=' . $import_log->id) ?>" target="_blank">Список ошибок</a>
<?php endif ?>