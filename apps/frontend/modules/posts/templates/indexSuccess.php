<?php include_partial('menu/portal-menu', array('post_type' => $post_type))?>
<div id="content">
  <div class="rc-box news">
      <div class="rc t"><div></div></div>
      <div class="content">

        <div class="articles-wide">

          <div class="col-left">
            <h2><?= '<span class="png24"></span>' . $sf_user->getAttribute('post_type_name') ?></h2>
            <?php if (count($posts) > 0): ?>
            <div class="item">
              <?php if ($photo = photo($posts[0], 279, 209)): ?>
                <?php if(empty($posts[0]->slug)): //TODO Remove when slugs for all posts will be updated ?>
                <?= link_to(image_tag($photo), '@post_show?id=' . $posts[0]->id . '&post_type=' . $post_type) ?>
                <?php else: ?>
                <?= link_to(image_tag($photo), '@post_show_slug?slug=' . $posts[0]->slug.'-'.$posts[0]->id . '&post_type=' . $post_type) ?>
                <?php endif ?>
              <?php endif ?>
            </div>
            <?php endif?>
          </div>
          <?php if (isset($posts[0])): ?>
            <div class="col-right">
              <div class="item">
                <?php include_partial('post_date_and_thems', array('post' => $posts[0])); ?>
                <h4><?php if(empty($posts[0]->slug)): //TODO Remove when slugs for all posts will be updated ?>
                  <?= link_to($posts[0]->title, '@post_show?id=' . $posts[0]->id . '&post_type=' . $post_type)?>
                <?php else: ?>
                  <?= link_to($posts[0]->title, '@post_show_slug?slug=' . $posts[0]->slug.'-'.$posts[0]->id . '&post_type=' . $post_type) ?>
                <?php endif ?></h4>
                <?= $posts[0]->lid ?>
              </div>
            </div>
          <?php endif ?>
        </div><!-- .articles-wide -->
        <?php if (isset($posts[1])): ?>
          <div class="col-left">
            <div class="item">
              <?php if ($photo = photo($posts[1], 193, 128)): ?>
                <?php if(empty($posts[1]->slug)): //TODO Remove when slugs for all posts will be updated ?>
                <?= link_to(image_tag($photo), '@post_show?id=' . $posts[1]->id . '&post_type=' . $post_type) ?>
                <?php else: ?>
                <?= link_to(image_tag($photo), '@post_show_slug?slug=' . $posts[1]->slug.'-'.$posts[1]->id . '&post_type=' . $post_type) ?>
                <?php endif ?>
              <?php endif ?>

              <?php include_partial('post_date_and_thems', array('post' => $posts[1])); ?>
              <h4><?php if(empty($posts[1]->slug)): //TODO Remove when slugs for all posts will be updated ?>
                <?= link_to($posts[1]->title, '@post_show?id=' . $posts[1]->id . '&post_type=' . $post_type)?>
              <?php else: ?>
                <?= link_to($posts[1]->title, '@post_show_slug?slug=' . $posts[1]->slug.'-'.$posts[1]->id . '&post_type=' . $post_type) ?>
              <?php endif ?></h4>
              <?= $posts[1]->lid ?>
            </div>
          </div>
        <?php endif ?>
        <?php if (isset($posts[2])): ?>
          <div class="col-right">
            <div class="item">
              <?php if (null != $posts[2]->title_photo): ?>
                <?php if ($photo = photo($posts[2], 193, 128)): ?>
                  <?php if(empty($posts[2]->slug)): //TODO Remove when slugs for all posts will be updated ?>
                  <?= link_to(image_tag($photo), '@post_show?id=' . $posts[2]->id . '&post_type=' . $post_type) ?>
                  <?php else: ?>
                  <?= link_to(image_tag($photo), '@post_show_slug?slug=' . $posts[2]->slug.'-'.$posts[2]->id . '&post_type=' . $post_type) ?>
                  <?php endif ?>
                <?php endif ?>
              <?php endif ?>
              <?php include_partial('post_date_and_thems', array('post' => $posts[2])); ?>
              <h4><?php if(empty($posts[2]->slug)): //TODO Remove when slugs for all posts will be updated ?>
                <?= link_to($posts[2]->title, '@post_show?id=' . $posts[1]->id . '&post_type=' . $post_type)?>
              <?php else: ?>
                <?= link_to($posts[2]->title, '@post_show_slug?slug=' . $posts[2]->slug.'-'.$posts[2]->id . '&post_type=' . $post_type) ?>
              <?php endif ?></h4>
              <?= $posts[2]->lid ?>
            </div>
          </div>
        <?php endif ?>
      </div><!-- .content -->
      <div class="rc b"><div></div></div>
    </div>
    <?php include_partial('banner/portal_zone_after_posts') ?>
    <?php include_partial('banner/block2-inline-spec') ?>
    <?php $i = 0; ?>
    <?php foreach ($themes as $id => $theme): ?>
      <?php $prefix = sprintf('%s', $id) ?>
      <?php cached_component(
        'posts',
        'themes',
        array(
          'post_type'   => $post_type,
          'theme_id'    => $id,
          'theme'       => $theme,
          'theme_count' => $i
        ),
        $cache_prefix . '_' . $prefix,
        rand(1200, 1900))
      ?>
      <?= prepare_openx_zone($i)?>
      <?php $i++; ?>
    <?php endforeach ?>
    <?php include_partial('banner/block5-down-spec') ?>
</div>

  <div id="aside">
    <?php include_partial('page/aside-head')?>

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