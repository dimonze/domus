<?php
$title_class = 'default';
if ($analytics->status == 'not_publish') {
  $title_class = 'inactive';
}else if ($analytics->on_main === true) {
  $title_class = 'on_main';
}
?>
<div name="title" class="status-<?= $title_class ?>">
  <?= $analytics->title ?>
</div>
<br />
<div name="lid">
  <?= $analytics->lid ?>
</div>