<?php use_helper('Tag')?>
<?php include_partial('menu/portal-menu', array(
  'post_type'     => 'news',
  'news_section'  => $news_section))?>
<div id="content">
  <div class="rc-box news realty">
    <!-- <div class="rc t"><div>&nbsp;</div></div> -->
    <div class="content">
      <div class="col-left">
        <h2><?='<span class="png24"></span>' . News::$sections[$news_section] ?></h2>
        <?php if (!empty($primary_news)): ?>
          <div class="item">
            <?php if ($photo = photo($primary_news, 279, 209)): ?>
              <?php if(empty($primary_news->slug)): //TODO Remove when slugs for all posts will be updated ?>
              <?= link_to(image_tag($photo), '@news_show?id='.$primary_news->id)?>
              <?php else: ?>
              <?= link_to(image_tag($photo), '@news_show_slug?slug='.$primary_news->slug.'-'.$primary_news->id)?>
              <?php endif ?>
            <?php endif ?>
            <h6><?= format_date($primary_news->created_at, 'd MMMM yyyy, HH:mm')?></h6>
            <h4><?php if(empty($primary_news->slug)): //TODO Remove when slugs for all posts will be updated ?>
              <?= link_to($primary_news->title, '@news_show?id='.$primary_news->id) ?>
              <?php else: ?>
              <?= link_to($primary_news->title, '@news_show_slug?slug='.$primary_news->slug.'-'.$primary_news->id) ?>
            <?php endif ?></h4>
            <?= $primary_news->lid?>
          </div>

        <?php elseif (isset($news) && count($news) > 0): ?>
          <?php $last_news = $news[0] ?>
          <?php unset($news[0])?>
          <div class="item">
            <?php if ($photo = photo($last_news, 279, 209)): ?>
              <?php if(empty($last_news->slug)): //TODO Remove when slugs for all posts will be updated ?>
              <?= link_to(image_tag($photo), '@news_show?id='.$last_news->id)?>
              <?php else: ?>
              <?= link_to(image_tag($photo), '@news_show_slug?slug='.$last_news->slug.'-'.$last_news->id)?>
              <?php endif ?>
            <?php endif ?>

            <h6><?= format_date($last_news->created_at, 'd MMMM yyyy, HH:mm')?></h6>
            <h4><?php if(empty($last_news->slug)): //TODO Remove when slugs for all posts will be updated ?>
              <?= link_to($last_news->title, '@news_show?id='.$last_news->id) ?>
              <?php else: ?>
              <?= link_to($last_news->title, '@news_show_slug?slug='.$last_news->slug.'-'.$last_news->id) ?>
            <?php endif ?></h4>
            <?= $last_news->lid?>
          </div>
        <?php endif ?>
      </div>
    <?php if (isset($news) && count($news) > 0): ?>
        <div class="col-right">
          <?php foreach($news as $new): ?>
          <div class="item">
            <h6><?= format_date($new->created_at, 'd MMMM yyyy, HH:mm')?></h6>
            <h4><?php if(empty($new->slug)): //TODO Remove when slugs for all posts will be updated ?>
              <?= link_to($new->title, '@news_show?id='.$new->id) ?>
              <?php else: ?>
              <?= link_to($new->title, '@news_show_slug?slug='.$new->slug.'-'.$new->id) ?>
            <?php endif ?></h4>
            <?= $new->lid ?>
          </div>
          <?php endforeach ?>
        </div>
    <?php endif ?>
    </div>
    <!-- <div class="rc b"><div>&nbsp;</div></div> -->
    <div class="b"></div>
  </div>
  <?php if ($news_section != 'news-portal'): ?>
    <?php include_partial('banner/portal_zone_after_posts') ?>
    <?php $i = 0; ?>
    <?php foreach ($themes as $id => $theme): ?>
      <?php $prefix = sprintf('%s_%s', $id, $news_section) ?>
      <?php cached_component('news', 'themeslist', array(
          'news_section' => $news_section,
          'theme_id' => $id,
          'theme' => $theme,
          'theme_count' => $i
        ),
        $cache_prefix . '_' . $prefix, rand(1200, 1900)
      ) ?>
      <?= prepare_openx_zone($i) ?>
      <?php $i++; ?>
    <?php endforeach ?>
  <?php else: ?>
    <?php include_partial('banner/portal_zone_after_posts') ?>
    <?php $prefix = sprintf('%s_index-other', $news_section) ?>
    <?php cached_component('news', 'portalothernews', array('page' => $page), $cache_prefix . '_' . $prefix, rand(1200, 1900)) ?>

  <?php endif ?>
  <?php include_partial('banner/block5-down-spec') ?>
</div>
<div id="aside">
  <?php include_partial('page/aside-head')?>
  <?php if ($news_section != 'news-portal'): ?>

    <?php
      cached_component('page', 'themeList', array(
        'type' => 'news',
        'current' => isset($current_theme) ? $current_theme : false,
        'news_section' => $news_section
        ),
        $cache_prefix . '_' . (int)sfConfig::get('is_new_building'),
        1900
      )
    ?>
  <?php endif ?>
  <?php include_partial('global/aside_search')?>
  <?php include_partial('banner/block3-right-spec') ?>
  <?php foreach ($sections as $id => $section): ?>
      <?php $prefix = sprintf('%s_%s', $section, 'component') ?>
      <?php cached_component('news', 'sectionlist', array('news_section' => $section, 'url' => $id), $cache_prefix . '_' . $prefix, rand(1500, 2500)) ?>
  <?php endforeach ?>
  <?php include_component('page', 'aside'); ?>
</div>