
<?php
$title_class = 'default';
if ($blog->status == 'inactive') {
  $title_class = 'inactive';
} elseif ($blog->status == 'active') {
  $title_class = 'on_main';
} elseif ($blog->status == 'moderate') {
  $title_class = 'moderate';
} elseif ($blog->status == 'restricted') {
  $title_class = 'restricted';
}

?>
<div name="title" class="status-<?= $title_class ?>">
<a href="#" class="view-blog-posts" ><?= $blog->title ?>
    <input type="hidden" name="blog_post_filters[blog_id]" value="<?= $blog->id ?>" />
</a>
</div>