<?php use_helper('Text')?>
<?php include_partial('menu/portal-menu', array(
  'post_type'   => $sf_user->getAttribute('post_type'),
  'post_title'  => $post->title))?>
<div id="content">
<div class="rc-box news author-post">
  <div class="rc t"><div></div></div>
  <div class="content box-post">

    <div class="rc-box-one-col">
      <h2><?= ($post->title_h1 != '') ? $post->title_h1 : $post->title ?></h2>
      <div class="item">
        <h4><?= $post->author_name ? $post->author_name : $post->User->name ?></h4>
        <?php include_partial('posts/post_date_and_thems', array('post' => $post)) ?>
        <?php if (null != $post->subtitle): ?>
        <h3><?= simple_format_text($post->subtitle) ?></h3>
        <?php endif ?>
        <?= simple_format_text($post->post_text) ?>
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
          'posts/post-actions',
          array(
            'url' => url_for(
              'qa/show?id='
              . $post->id,
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

  <?php if (count($qa_list)): ?>
  <div class="rc-box qw-box">
    <div class="rc t"><div></div></div>
    <div class="content">
      <h2><span></span>Вопросы по теме</h2>
      <br/>
      <?php foreach ($qa_list as $question): ?>
        <div class="bubble-wrp">
          <?= image_tag(photo($question->User, 50, 50), 'class=bubble-foto') ?>
          <div class="bubble3">

            <div class="tail-l png24"></div>
            <div class="tl bbl-corn png24"></div><div class="tr bbl-corn png24"></div>
            <div class="bubble3-content">
              <h4><?= link_to($question->title, 'qa_show', array('id' => $question->id)); ?></a></h4>
              <p>
                <?= $question->post_text ?>
              </p>
              <h5 class="qw-info">
                <?= $question->author_name ? $question->author_name : $question->User->name ?>
                , <?= format_date($question->created_at, 'd MMMM, HH:mm') ?>
              </h5>
              <?php if (count($themes = $question->Themes)): ?>
                <h5>
                  <?php $i = 0;
                  foreach ($themes as $theme) {
                    echo (0 == $i) ? $theme->title : ', ' . $theme->title;
                    $i++;
                  } ?>
                </h5>
                  <?php endif; ?>
            </div>
            <div class="bl bbl-corn png24"></div><div class="br bbl-corn png24"></div>
          </div>
        </div>
<?php endforeach; ?>
      </div><!-- .content -->
      <div class="rc b"><div></div></div>
    </div><!-- .rc-box + .qw-box -->
    <?php endif; ?>

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
      <?php $i++; ?>
    <?php endforeach ?>
  <?php endif ?>
  <?php cached_component('expert_article', 'list', null, $cache_prefix, 1900) ?>
  <?php cached_component('author_article', 'list', null, $cache_prefix, 1700) ?>
  <?php include_partial('banner/block5-down-spec') ?>
</div>

<div id="aside">
  <?php include_partial('page/aside-head') ?>
  <?php
    cached_component('page', 'themeList', array(
      'type' => 'qa',
      'current' => isset($current_theme) ? $current_theme : false
      ),
      $cache_prefix,
      1900
    )
  ?>
  <?php include_partial('banner/block3-right-spec') ?>
  <?php include_component('page', 'aside'); ?>
</div>