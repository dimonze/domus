<?php include_partial('menu/blogs-menu', array('action' => $action, 'theme' => $post_theme)) ?>

<div id="content">
<div class="rc-box events header-bl-box themes-opinions-main-box">
  <div class="rc t"><div></div></div>
  <div class="content">
    <h2><?= $post_theme->title ?></h2>
    <?php if ($pager->getNbResults()): ?>
      <?php foreach ($pager->getResults() as $article): ?>
        <div class="item">
          <?= image_tag(photo($article->Blog->User)) ?>
          <h4 class="opinion-auth-name"><?= $article->Blog->User->name?></h4>
          <div class="author-company"><?= $article->Blog->User->company_name ?></div>

          <?php include_partial('blogs/post_date_and_thems', array('post' => $article)) ?>
          <h4><?= link_to($article->title, '@blog_post_show?blog_url=' . $article->Blog->url . '&id=' . $article->id) ?></h4>
          <?= $article->lid ?>
          <h6 class="gray">Тема: <?= $post_theme->title ?></h6>
        </div>
      <?php endforeach ?>
      <?php include_partial('posts/paginator', array('pager' => $pager))?>
    <?php endif ?>

  </div><!-- .content -->

  <div class="rc b"><div></div></div>

</div>
<?php cached_component('expert_article', 'list', null, $cache_prefix, 1900) ?>
<?php include_partial('banner/block5-down-spec') ?>
</div>
<div id="aside">
  <?php include_partial('page/aside-head')?>
  <?php cached_component('blogs', 'list', null, $cache_prefix, 1500) ?>
  <?php if (count($themes)): ?>
     <?php include_partial('blogs/blogthemes', array('themes' => $themes)) ?>
  <?php endif ?>
  <?php include_partial('banner/block3-right-spec') ?>
  <?php include_component('page', 'aside'); ?>
</div>