<?php include_partial('menu/blogs-menu', array('action' => $action, 'post' => $post)) ?>

<div id="content">
  <div class="rc-box news author-post">
    <div class="rc t"><div></div></div>
    <div class="content box-post">

      <div class="rc-box-one-col">
        <h2><?= $post->title ?></h2>
        <div class="item">
          <div class="author-post-actions">
            <a href="<?= url_for('@pm?action=add&to=' . $author->id) ?>" class="write2author popup" p="create-pm" rel="reg"><span class="png24"></span>Написать автору</a>
          </div>
          <?= image_tag(photo($author, 150, 150, 'pict_user.png')) ?>
          <h4><?= $author->name ?>
            <div class="author-comm">
              <?php if ($author->Info->about): ?>
                <?= $author->Info->about ?>
              <?php endif ?>
            </div>
          </h4>
          <?php include_partial('blogs/post_date_and_thems', array('post' => $post)) ?>
          <?= $post->body ?>
        </div>
        <?php
          include_partial(
            'posts/post-actions', array(
              'url' => url_for(
                '@blog_post_show?blog_url='
                . $post->Blog->url
                . '&id=' . $post->id,
                true
              ),
              'status' => urlencode($post->title)
            )
          )
        ?>
        <?php
          include_partial(
            'comments/comments',
            array(
              'comments'  => $post->Comments,
              'post'      => $post
            )
          )
        ?>
      </div><!-- .rc-box-one-col -->
    </div><!-- .box-post -->
    <div class="rc b"><div></div></div>
  </div><!-- .rc-box + .news -->

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