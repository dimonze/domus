<?php if (preg_match('/^(http)/',$import_log->file_name) || preg_match('/^(ftp)/',$import_log->file_name)): ?>
  <span style="color: red;">автоимпорт</span>
<?php else: ?>
  <span style="color: green;">загружен</span>
<?php endif ?>
