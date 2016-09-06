<?php
$title_class = 'default';
if ($expert_article->status == 'inactive') {
  $title_class = 'inactive';
}else if ($expert_article->on_main === true) {
  $title_class = 'on_main';
}
?>
<div name="title" class="status-<?= $title_class ?>">
  <?= $expert_article->title ?>
</div>
<br />
<div name="lid">
  <?= $expert_article->lid ?>
</div>