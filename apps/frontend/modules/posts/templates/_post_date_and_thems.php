<h6 class="theme-of"><?= format_date($post->created_at, 'd MMMM yyyy') ?><?php if (count($themes = $post->Themes)): ?>
<?php
  foreach ($themes as $theme) {
    echo ', ' . $theme->title;
  }
?>
<?php endif; ?>
</h6>