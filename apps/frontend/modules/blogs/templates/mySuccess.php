<?php include_partial('menu/blogs-menu', array('action' => $action)) ?>

<div id="content">
  <?php include_partial('import/package-import-adv') ?>
  <?php include_component('pm','profilemessages') ?>
    <?php if ($sf_user->Blog->status == 'active'): ?>
    <div class="rc-box news authors-main-box">
      <div class="rc t"><div></div></div>
      <div class="content">
        <div class="authors-main-wrp">
          <?= link_to('Добавить запись<span></span>', '@blog_post_add', array('class' => 'blue-button', 'id' => 'make-q')) ?>
        	<h2>
          <?= $user->name ?>
          <?php if ($user->company_name): ?>
            <div class="author-comm">
              <?= $user->company_name ?>
            </div>
          <?php endif ?>
        	</h2>
        	<div class="author-info">
            <?= image_tag(photo($user)) ?>

            <?php if ($user->Info->about): ?>
              <?= $user->Info->about ?>
            <?php endif ?>
        	</div>

        <?php if ($pager->getNbResults()): ?>
          <?php foreach ($pager->getResults() as $article): ?>
            <div class="item">
              <?php  include_partial('blogs/post_date_and_thems', array('post' => $article)); ?>
              <h4><?= link_to($article->title, '@blog_post_show?blog_url=' . $article->Blog->url . '&id=' . $article->id) ?></h4>
              <?= $article->lid ?>
              <?php include_partial('blog_post_actions', array('article' => $article)) ?>
            </div>
          <?php endforeach ?>
          <?php include_partial('global/posts-paginator', array('pager' => $pager))?>
        <?php endif ?>
      </div>

      </div><!-- .content -->
      <div class="rc b"><div></div></div>
    </div><!-- .rc-box + .authors-box -->
    <?php elseif ($sf_user->Blog->status == 'moderate'): ?>
      <h3>Ваш блог проходит модерацию</h3>
    <?php else: ?>
      <form action="<?= url_for('user/create-blog') ?>" method="post">
        <?= link_to('Создать блог<span></span>', 'form/createblog', array('class' => 'blue-button inner popup', 'rel' => 'reg')) ?>
      </form>
    <?php endif ?>

      <div class="rc b"><div></div></div>
    </div>


<div id="aside">
  <?php include_partial('page/aside-head')?>
  <?php include_component('menu', 'user') ?>
</div>