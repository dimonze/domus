<?php
$title_class = 'default';
if ($author_article->status == 'inactive') {
  $title_class = 'inactive';
}else if ($author_article->on_main === true) {
  $title_class = 'on_main';
}
?>
<div name="title" class="status-<?= $title_class ?>">
  <?= $author_article->title ?>
</div>
<br />
<div name="lid">
  <?= $author_article->lid ?>
</div>