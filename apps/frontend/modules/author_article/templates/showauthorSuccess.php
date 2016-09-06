<?php include_partial('menu/portal-menu', array(
  'post_type' => 'author_article',
  'author'    => $author)) ?>

<div id="content">

    <div class="rc-box news authors-main-box">
      <div class="rc t"><div></div></div>
      <div class="content">
        <div class="authors-main-wrp">
        	<h2>
          <?= $author->name ?>
          <?php if ($author->company): ?>
            <div class="author-comm">
              <?php if ($author->post): ?>
                <?= $author->post ?>,&nbsp;
              <?php endif ?>
              <?= $author->company ?>
            </div>
          <?php endif ?>
        	</h2>
        	<div class="author-info">
          <?= image_tag(photo($author)) ?>
          <?php if ($author->description): ?>
            <?= $author->description?>
          <?php endif ?>
        	</div>
        <?php if ($pager->getNbResults()): ?>
          <?php foreach ($pager->getResults() as $article): ?>
            <div class="item">
              <?php include_partial('posts/post_date_and_thems', array('post' => $article)); ?>
              <h4><?php if(empty($article->slug)): //TODO Remove when slugs for all posts will be updated ?>
                <?= link_to($article->title, '@author_article_show?author_id=' . $author->id . '&id=' . $article->id) ?>
                <?php else: ?>
                <?= link_to($article->title, '@author_article_show_slug?author_id=' . $author->id . '&slug=' . $article->slug.'-'.$article->id) ?>
              <?php endif ?></h4>
              <?= $article->lid ?>
            </div>
          <?php endforeach ?>
          <?php include_partial('global/posts-paginator', array('pager' => $pager))?>
        <?php endif ?>
      </div>

      </div><!-- .content -->
      <div class="rc b"><div></div></div>
    </div><!-- .rc-box + .authors-box -->


      <div class="rc b"><div></div></div>
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