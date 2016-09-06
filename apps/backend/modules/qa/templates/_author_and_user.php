<?php if ($post->author_name): ?>
  <div class="author_name">
    <?= $post->author_name ?>
  </div>
<?php elseif($post->User->name): ?>
  <div class="user_name">
    <?= link_to($post->User->name, 'user/edit?id=' . $post->User->id) ?>
  </div>
<?php endif ?>