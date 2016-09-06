
<?php
$title_class = 'default';
if ($blog_post->status == 'not_publish') {
  $title_class = 'inactive';
} elseif ($blog_post->status == 'publish') {
  $title_class = 'on_main';
} elseif ($blog_post->status == 'moderate') {
  $title_class = 'moderate';
} elseif ($blog_post->status == 'restricted') {
  $title_class = 'restricted';
}

?>
<div name="title" class="status-<?= $title_class ?>">
<?= $blog_post->title ?><br />
<small><?= $blog_post->lid ?></small>
</div>