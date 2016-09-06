<?php if (preg_match('/^(http)/',$import_log->file_name) || preg_match('/^(ftp)/',$import_log->file_name)): ?>
  <a href="<?= $import_log->file_name ?>" target="_blank"><?= $import_log->file_name ?></a>
<?php else: ?>
  <?= $import_log->file_name ?>
<?php endif ?>
