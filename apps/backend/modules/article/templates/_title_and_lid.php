<?php
$title_class = 'default';
if ($article->status == 'not_publish') {
  $title_class = 'inactive';
}else if ($article->on_main === true) {
  $title_class = 'on_main';
}
?>
<div name="title" class="status-<?= $title_class ?>">
  <?= $article->title ?>
</div>
<br />
<div name="lid">
  <?= $article->lid ?>
</div>