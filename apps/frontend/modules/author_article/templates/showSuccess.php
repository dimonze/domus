<?php if ($photo = photo($author)): ?>
  <?php slot('facebook') ?>
    <meta property="og:image" content="<?= $photo ?>" />
  <?php end_slot() ?>
<?php endif ?>

<?php include_partial('menu/portal-menu', array(
  'post_type'   => 'author_article',
  'author'      => $author,
  'post_title'  => $article->title)) ?>

<div id="content">
  <div class="rc-box news author-post">
    <div class="rc t"><div></div></div>
    <div class="content box-post">

      <div class="rc-box-one-col">
        <h2><?= ($article->title_h1 != '') ? $article->title_h1 : $article->title ?></h2>
        <div class="item">
          <div class="author-post-actions">

          </div>
            <?php if ($photo = photo($author)): ?>
              <?= link_to(
                image_tag($photo, 'class=float-img'),
                '@author_article_show_author?author_id='.$author->id
              ) ?>
            <?php endif ?>

          <h4><a href="<?= url_for('@author_article_show_author?author_id='.$author->id) ?>"><?= $author->name ?></a>
            <?php if ($author->company): ?>
              <div class="author-comm">
                <?php if ($author->post): ?>
                  <?= $author->post ?>,&nbsp;
                <?php endif ?>
                <?= $author->company ?></div>
            <?php endif ?>
          </h4>
          <?php include_partial('posts/post_date_and_thems', array('post' => $article)) ?>
          <?= $paginate['data'] ?>
        <?php include_partial('posts/p_paginator', array('pager' => $paginate)) ?>
        </div>
        <?php
          include_partial('posts/post-actions', array(
            'url' => url_for(
                'author_article/show?author_id='
                . $author->id
                . '&id=' . $article->id,
                true
              ),
              'status' => urlencode($article->title)
            )
          )
        ?>
        <?php
        include_partial(
          'comments/comments',
          array(
            'comments' => $article->Comments,
            'post' => $article
          )
        )
      ?>
      </div><!-- .rc-box-one-col -->
    </div><!-- .box-post -->
    <div class="rc b"><div></div></div>
  </div><!-- .rc-box + .news -->

  <?php cached_component('author_article', 'otherposts', array('article_id' => $article->id, 'author_id' => $article->author_id), $cache_prefix, rand('2000', '4000'))?>
  <?php cached_component('expert_article', 'list', null, $cache_prefix, 1900) ?>
  <?php include_partial('banner/block5-down-spec') ?>
</div>

<div id="aside">
  <?php include_partial('page/aside-head')?>
  <?php cached_component('author_article', 'authors', array('current_author_id' => $author->id), $cache_prefix, 1500) ?>
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