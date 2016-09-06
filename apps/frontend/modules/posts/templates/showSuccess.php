<?php use_helper('Text')?>

<?php if ($photo = photo($post, 281, 209)): ?>
  <?php slot('facebook') ?>
    <meta property="og:image" content="<?= $photo ?>" />
  <?php end_slot() ?>
<?php endif ?>

<?php include_partial('menu/portal-menu', array(
  'post_type'   => $post_type,
  'post_title'  => $post->title))?>

<div id="content">
  <div class="rc-box news">
    <div class="rc t"><div></div></div>
    <div class="content box-post">

    <div class="rc-box-one-col">
      <h1><?= ($post->title_h1 != '') ? $post->title_h1 : $post->title ?></h1>
        <div class="item">
          <div class="float-img">
            <?php if ($photo): ?>
              <?= image_tag($photo) ?>

              <?php if ($post->title_photo_source): ?>
                <?php if ($post->title_photo_source_url): ?>
                  <?php $photo_source = link_to($post->title_photo_source, $post->title_photo_source_url, array('target' => '_blank'))?>
                <?php else: ?>
                  <?php $photo_source = $post->title_photo_source ?>
                <?php endif ?>
                <span class="post-foto-comment">Фото: <?= $photo_source ?></span>
              <?php endif ?>
            <?php endif ?>
          </div>
          
          <?php if (null != $post->subtitle): ?>
            <h3><?= $post->subtitle ?></h3>
          <?php endif ?>
          <?php include_partial('posts/post_date_and_thems', array('post' => $post)); ?>
          <?= $paginate['data'] ?>
          <?php include_partial('p_paginator', array('pager' => $paginate)) ?>
          <?php if(null != ($post->event_date || $post->event_place || $post->event_contact)): ?>
          <div class="item-info-block margin-t-min">

            <div class="corn tl"></div>
            <div class="corn tr"></div>
            <div class="corn bl"></div>
            <div class="corn br"></div>
            <div class="info-block-wrp">
              <p>Выставка проходит <?= $post->event_date ?></p>
              <p>Место проведения: <?= $post->event_place ?></p>
              <p><?= auto_link_text($post->event_contact) ?></p>

            </div>
          </div>
          <?php endif ?>
        </div>
      
        <div class="about-post">
          <?php if (null != $post->source): ?>
            <?php if (null != $post->source_url): ?>
              <?php $source = link_to($post->source, $post->source_url, array('target' => '_blank'))?>
            <? else: ?>
              <?php $source = $post->source ?>
            <?php endif ?>
            <div class="post-from">Источник: <?= $source ?></div>
          <?php endif ?>
          <?php if ($post->less_count > 0): ?>
            <div class="reads">Прочитано <?= $post->less_count ?> раз</div>
          <?php endif ?>
        </div>
      <?php
        include_partial(
          'posts/post-actions', array(
            'url' => url_for(
              'posts/show?post_type='
              . $post->post_type
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
            'comments' => $post->Comments,
            'post' => $post
          )
        )
      ?>
    </div><!-- .rc-box-one-col -->
  </div><!-- .box-post -->
  <div class="rc b"><div></div></div>
</div>
  <?php include_partial('banner/portal_zone_after_posts') ?>
  <?php if (isset($post_themes)): ?>
    <?php $i = 0; ?>
    <?php foreach ($post_themes as $id => $theme): ?>
      <?php $prefix = sprintf('_%s', $id) ?>
      <?php cached_component(
        'posts',
        'themes',
        array(
          'post_type' => $post->post_type,
          'theme_id' => $id,
          'theme' => $theme,
          'theme_count' => $i,
          'post_id'     => $post->id
        ),
        $cache_prefix . '_' . $prefix,
        rand(1200, 1900))
      ?>
      <?= prepare_openx_zone($i)?>
      <?php $i++; ?>
    <?php endforeach ?>
  <?php endif ?>


  <?php include_partial('banner/post_themes') ?>
  <?php cached_component('expert_article', 'list', null, $cache_prefix, 1900) ?>
  <?php include_partial('banner/post_themes') ?>
  <?php cached_component('author_article', 'list', null, $cache_prefix, 1700) ?>
  <?php include_partial('banner/block5-down-spec') ?>
</div>

<div id="aside">
  <?php include_partial('page/aside-head') ?>

  <?php
    cached_component('page', 'themeList', array(
      'type' => $post_type,
      'current' => isset($current_theme) ? $current_theme : false
      ),
      $cache_prefix,
      1900
    )
  ?>
  <?php include_partial('global/aside_search')?>
  <?php include_partial('banner/block3-right-spec') ?>
  <?php include_component('page', 'aside'); ?>
</div>