<?php include_partial('menu/portal-menu', array(
  'post_type' => 'qa',
  'theme' => $qa_theme))?>
<div id="content">
    <div class="rc-box qw-box qw-theme-box">
      <div class="rc t"><div></div></div>
      <div class="content">
        <?= link_to('Задать вопрос<span></span>', '/qa/add', array('class' => 'blue-button', 'id' => 'make-q')) ?>
          <h2>Вопросы и ответы &mdash; <?= $qa_theme->title?></h2>
        <?php include_partial('qa/qa_list', array('qa_list' => $pager->getResults()))?>
        <?php include_partial('global/posts-paginator', array('pager' => $pager))?>
      </div><!-- .content -->
      <div class="rc b"><div></div></div>
    </div><!-- .rc-box + .qw-box -->

  <?php cached_component('expert_article', 'list', null, $cache_prefix, 1900) ?>
  <?php cached_component('author_article', 'list', array('news' => true), $cache_prefix, 1700) ?>
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