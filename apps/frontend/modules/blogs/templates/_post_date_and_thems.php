<h6 class="date-of"><?= format_date($post->created_at, 'd MMMM yyyy') ?></h6>
<?php if (count($theme = $post->Theme)): ?>
  <h6 class="theme-of"><?= $theme->title ?></h6>
<?php endif; ?>