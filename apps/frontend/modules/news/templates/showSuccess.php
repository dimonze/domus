<?php if ($photo = photo($news, 281, 209)): ?>
  <?php slot('facebook') ?>
    <meta property="og:image" content="<?= $photo ?>" />
  <?php end_slot() ?>
<?php endif ?>

<?php include_partial('menu/portal-menu', array(
  'post_type'   => 'news',
  'news_section' => $news_section,
  'post_title' => $news->title))?>

<div id="content">
  <div class="rc-box news">
    <div class="rc t"><div></div></div>
    <div class="content box-post">

      <div class="rc-box-one-col">
        <h2><?= ($news->title_h1 != '') ? $news->title_h1 : $news->title ?></h2>
        <div class="item">
          <?php if ($photo = photo($news, 281, 209)): ?>
            <div class="float-img">
              <?= image_tag($photo) ?>

              <?php if ($news->title_photo_source): ?>
                <?php if ($news->title_photo_source_url): ?>
                  <?php $photo_source = link_to($news->title_photo_source, $news->title_photo_source_url, array('target' => '_blank'))?>
                <?php else: ?>
                  <?php $photo_source = $news->title_photo_source ?>
                <?php endif ?>
                <span class="post-foto-comment">Фото: <?= $photo_source ?></span>
              <?php endif ?>
            </div>
          <?php endif ?>

          <?php if (null != $news->subtitle): ?>
            <h3><?= $news->subtitle ?></h3>
          <?php endif ?>
          <?php include_partial('posts/post_date_and_thems', array('post' => $news)); ?>
          <?= $news->post_text ?>

        </div>

        <div class="about-post">
          <?php if (null != $news->source): ?>
            <?php if (null != $news->source_url): ?>
              <?php $source = link_to($news->source, $news->source_url, array('target' => '_blank'))?>
            <? else: ?>
              <?php $source = $news->source ?>
            <?php endif ?>
            <div class="post-from">Источник: <?= $source ?></div>
          <?php endif ?>
          <?php if ($news->less_count > 0): ?>
            <div class="reads">Прочитано <?= $news->less_count ?> раз</div>
          <?php endif ?>
        </div>
      <?php
        include_partial(
          'posts/post-actions',
          array(
            'url' => url_for(
              'news/show?id='
              . $news->id,
              true
            ),
            'status' => urlencode($news->title)
          )
        )
      ?>
      <?php
        include_partial(
          'comments/comments',
          array(
            'comments' => $news->Comments,
            'post' => $news,
          )
        )
      ?>
      </div><!-- .rc-box-one-col -->
    </div><!-- .box-post -->
    <div class="rc b"><div></div></div>
  </div><!-- .rc-box + .news -->

  <?php include_partial('banner/portal_zone_after_posts') ?>
  <?php include_partial('banner/news_after_banner') ?>

  <?php if ($news_section != 'news-portal'): ?>
    <?php if (isset($news_themes)): ?>
      <?php $i = 0; ?>
      <?php foreach ($news_themes as $id => $theme): ?>
        <?php $prefix = sprintf('%s_%s', $id, $news_section) ?>
        <?php cached_component('news', 'themeslist', array(
            'news_section' => $news_section,
            'theme_id'     => $id,
            'theme'        => $theme,
            'news_id'      => $news->id,
            'theme_count'  => $i
          ),
          $cache_prefix . '_' . $prefix, rand(1200, 1900)) ?>
        <?= prepare_openx_zone($i) ?>
        <?php $i++; ?>
      <?php endforeach ?>
    <?php endif ?>
  <?php else: ?>
    <?php $prefix = sprintf('%s_%s-latest', $news->id, $news_section) ?>
    <?php cached_component('news', 'portallatest', array('news_id' => $news->id ), $cache_prefix . '_' . $prefix, rand(1200, 1900)) ?>
  <?php endif ?>
  <?php cached_component('expert_article', 'list', null, $cache_prefix, 1900) ?>
  <?php include_partial('banner/news') ?>
  <?php cached_component('author_article', 'list', array('news' => true), $cache_prefix, 1700) ?>
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
        $cache_prefix,
        1900
      )
    ?>
    <?php include_partial('global/aside_search')?>
    <?php include_partial('banner/block3-right-spec') ?>
    <?php endif ?>
  <?php include_component('page', 'aside'); ?>
</div>
