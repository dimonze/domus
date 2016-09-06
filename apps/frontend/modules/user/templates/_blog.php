<?php if ($sf_user->hasCredential('admin-access')): ?>
  <div class="profileBox">
    <h2>Мой блог</h2>
    <?php if ($sf_user->Blog->status == 'active'): ?>
      <h3><?= link_to($sf_user->Blog->title, '@blog_show?blog_url=' . $sf_user->Blog->url) ?></h3>
    <?php elseif ($sf_user->Blog->status == 'moderate'): ?>
      <h3>Ваш блог проходит модерацию</h3>
    <?php else: ?>
      <form action="<?= url_for('/user/create-blog') ?>" method="post">
        <h3><?= link_to('Создать блог', '/form/createblog', 'class=inner popup rel=reg') ?></h3>
      </form>
    <?php endif ?>
  </div>
<?php endif ?>