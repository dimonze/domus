<?php include_partial('menu/portal-menu', array(
  'post_type' => 'author_article',
  'theme'     => $post_theme)) ?>
<div id="content">
<div class="rc-box events header-bl-box themes-opinions-main-box">
  <div class="rc t"><div></div></div>
  <div class="content">
    <h2><?= $post_theme->title ?></h2>
    <?php if ($pager->getNbResults()): ?>
      <?php foreach ($pager->getResults() as $article): ?>
        <div class="item">
          <?= image_tag(photo($article->PostAuthor), 'class=float-img') ?>
          <h4 class="opinion-auth-name"><?= $article->PostAuthor->name ?></h4>
          <div class="author-company"><?= $article->PostAuthor->company ?></div>
          <span class="date2">
            <?= format_date($article->created_at, 'd MMMM yyyy, HH:mm') ?>
          </span>
          <h4><?php if(empty($article->slug)): //TODO Remove when slugs for all posts will be updated ?>
            <?= link_to($article->title, '@author_article_show?author_id=' . $article->PostAuthor->id . '&id=' . $article->id) ?>
            <?php else: ?>
            <?= link_to($article->title, '@author_article_show_slug?author_id=' . $article->PostAuthor->id . '&slug=' . $article->slug.'-'.$article->id) ?>
          <?php endif ?></h4>
          <?= $article->lid ?>
          <h6 class="gray">Тема: <?= $post_theme->title ?></h6>
        </div>
      <?php endforeach ?>
      <?php include_partial('paginator', array('pager' => $pager))?>
    <?php endif ?>

  </div><!-- .content -->

  <div class="rc b"><div></div></div>

</div>
<?php cached_component('expert_article', 'list', null, $cache_prefix, 1900) ?>
<?php include_partial('banner/block5-down-spec') ?>
</div>
<div id="aside">
  <?php include_partial('page/aside-head')?>
  <?php cached_component('author_article', 'authors', null, $cache_prefix, 1500) ?>
  <?php include_partial('global/aside_search')?>
  <?php
    cached_component('page', 'themeList', array(
      'type' => 'author_article',
      'current' => isset($current_theme) ? $current_theme : false
      ),
      $cache_prefix,
      1900
    )
  ?>
  <?php include_partial('banner/block3-right-spec') ?>
  <?php include_component('page', 'aside'); ?>
</div>