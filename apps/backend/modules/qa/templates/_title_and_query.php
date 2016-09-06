<?php
$title_class = 'default';
if ($post->status == 'not_publish') {
  $title_class = 'inactive';
}else if ($post->status == 'publish') {
  $title_class = 'on_main';
} else if ($post->status == 'moderate') {
  $title_class = 'moderate';
}

?>
<div name="title" class="status-<?= $title_class ?>">
  <?= $post->title ?>
</div>
<br />
<div name="lid">
  <?= $post->post_text ?>
</div>