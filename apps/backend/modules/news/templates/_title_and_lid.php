<?php
$title_class = 'default';
if ($news->status == 'inactive') {
  $title_class = 'inactive';
}else if ($news->is_primary === true) {
  $title_class = 'is_primary';
}else if ($news->on_main === true) {
  $title_class = 'on_main';
}
?>
<div name="title" class="status-<?= $title_class ?>">
  <?= $news->title ?>
</div>
<br />
<div name="lid">
  <?= $news->lid ?>
</div>