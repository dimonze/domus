<?php
$title_class = 'default';
if ($events->status == 'not_publish') {
  $title_class = 'inactive';
}else if ($events->on_main === true) {
  $title_class = 'on_main';
}
?>
<div name="title" class="status-<?= $title_class ?>">
  <?= $events->title ?>
</div>
<br />
<div name="lid">
  <?= $events->lid ?>
</div>