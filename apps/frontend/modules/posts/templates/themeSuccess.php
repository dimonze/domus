<?php include_partial('menu/portal-menu', array(
  'post_type' => $post_type,
  'theme' => $post_theme))?>
<div id="content">
  <div class="rc-box events header-bl-box themes-main-box">
    <div class="rc t"><div></div></div>
    <div class="content">
      <h2><?= $post_theme->title?></h2>
      <?php if (!$pager->getNbResults()): ?>
        <div class="item last-item">
          <p>Отсутствуют статьи по данной теме.</p>
        </div>
      </div>
      <?php else: ?>
      <?php $last_id = count($pager->getNbResults()) ?>
      <?php foreach ($pager->getResults() as $id => $post): ?>
          <?php if ($last_id == $id): ?>
            <div class="item last-item">
          <?php else: ?>
            <div class="item">
          <?php endif ?>

          <?php if ($photo = photo($post, 148, 98)): ?>
            <?php if(empty($post->slug)): //TODO Remove when slugs for all posts will be updated ?>
            <?= link_to(image_tag($photo), '@post_show?id=' . $post->id . '&post_type=' . $post->post_type) ?>
            <?php else: ?>
            <?= link_to(image_tag($photo), '@post_show_slug?slug=' . $post->slug.'-'.$post->id . '&post_type=' . $post->post_type) ?>
            <?php endif ?>
          <?php endif ?>

          <h6><?= format_date($post->created_at, 'd MMMM yyyy')?></h6>
          <h4><?php if(empty($post->slug)): //TODO Remove when slugs for all posts will be updated ?>
            <?= link_to($post->title, '@post_show?id=' . $post->id . '&post_type=' . $post->post_type)?>
          <?php else: ?>
            <?= link_to($post->title, '@post_show_slug?slug=' . $post->slug.'-'.$post->id . '&post_type=' . $post->post_type) ?>
          <?php endif ?></h4>
          <?= $post->lid ?>
        </div>
        <?php endforeach ?>
        <?php include_partial('paginator', array('pager' => $pager))?>
      </div>
        <?php endif ?>
    <div class="rc b"><div></div></div>
  </div>
  <?php include_partial('banner/post_themes')?>
  <?php cached_component('expert_article', 'list', null, $cache_prefix, 1900) ?>
  <?php include_partial('banner/post_themes')?>
  <?php cached_component('author_article', 'list', array('news' => true), $cache_prefix, 1700) ?>
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
  <?php include_partial('global/aside_search') ?>
  <?php include_partial('banner/block3-right-spec') ?>
  <?php include_component('page', 'aside') ?>
</div>