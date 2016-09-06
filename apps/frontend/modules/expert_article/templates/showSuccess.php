<?php if ($photo = photo($author)): ?>
  <?php slot('facebook') ?>
    <meta property="og:image" content="<?= $photo ?>" />
  <?php end_slot() ?>
<?php endif ?>

<?php include_partial('menu/portal-menu', array(
  'post_type'   => 'expert_article',
  'author'      => $author,
  'post_title'  => $article->title)) ?>

<div id="content">
<div class="rc-box news">
  <div class="rc t"><div></div></div>
  <div class="content box-post author-post">

    <div class="rc-box-one-col">
      <h2><?= ($article->title_h1 != '') ? $article->title_h1 : $article->title ?></h2>
      <div class="item">
        <?= image_tag(photo($author), 'class=float-img') ?>

        <h4><a href="<?= url_for('@expert_article_show_author?author_id='.$author->id) ?>"><?= $author->name ?></a>
          <?php if ($author->company): ?>
            <div class="author-comm">
              <?php if ($author->post): ?>
                <?= $author->post ?>,&nbsp;
              <?php endif ?>
              <?= $author->company ?></div>
          <?php endif ?>
        </h4>

        <?php include_partial('posts/post_date_and_thems', array('post' => $article)) ?>

        <?= image_tag(photo($article, 281, 209), 'class=float-img') ?>

        <?= $paginate['data'] ?>
        <?php include_partial('posts/p_paginator', array('pager' => $paginate)) ?>
      </div>
      <div class="about-post">
        <?php if (null != $article->source): ?>
          <?php if (null != $article->source_url): ?>
            <?php $source = link_to($article->source, $article->source_url, array('target' => '_blank'))?>
          <? else: ?>
            <?php $source = $article->source ?>
          <?php endif ?>
          <div class="post-from">Источник: <?= $source ?></div>
        <?php endif ?>
      </div>
      <?php
        include_partial(
          'posts/post-actions', array(
            'url' => url_for(
                'expert_article/show?author_id='
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
</div>
  <?php cached_component('expert_article', 'otherposts', array('article_id' => $article->id, 'author_id' => $article->author_id), $cache_prefix, rand('2000', '4000'))?>
  <?php cached_component('author_article', 'list', null, $cache_prefix, 1900) ?>
  <?php include_partial('banner/block5-down-spec') ?>
</div>

<div id="aside">
  <?php include_partial('page/aside-head')?>
  <?php cached_component('expert_article', 'authors', array('current_author_id' => $author->id), $cache_prefix, 1500) ?>
  <?php include_partial('global/aside_search')?>
  <?php
    cached_component('page', 'themeList', array(
      'type' => 'expert_article',
      'current' => isset($current_theme) ? $current_theme : false
      ),
      $cache_prefix,
      1900
    )
  ?>
  <?php include_partial('banner/block3-right-spec') ?>
  <?php include_component('page', 'aside') ?>
</div>