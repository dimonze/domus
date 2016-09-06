<div class="rc-box news themes authors-blue blue-box">
  <div class="rc t"><div></div></div>
  <div class="content">
    <div class="col-left">
      <h2><span class="png24"></span>Блоги</h2>
      <?php if (!isset($current_blog_id)): ?>
        <?php $current_blog_id = null ?>
      <?php endif ?>
      <?php if ($blogs): ?>
        <ul>
          <?php foreach ($blogs as $blog): ?>
            <?php if ($current_blog_id == $blog->id): ?>
              <li><strong><?= link_to($blog->title, '@blog_show?blog_url=' . $blog->url) ?></strong></li>
            <?php else: ?>
              <li><?= link_to($blog->title, '@blog_show?blog_url=' . $blog->url) ?></li>
            <?php endif ?>
          <?php endforeach ?>
        </ul>
      <?php endif ?>
    </div>
  </div>
  <div class="rc b"><div></div></div>
</div>