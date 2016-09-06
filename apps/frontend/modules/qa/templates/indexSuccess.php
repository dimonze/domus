<?php include_partial('menu/portal-menu', array('post_type' => 'qa'))?>
<?php if($sf_user->hasFlash('qa_success')): ?>
  <div class="flash success">
    <?= $sf_user->getFlash('qa_success') ?>
  </div>
<?php endif ?>

<div id="content">
  <?php foreach ($pager->getResults() as $id => $question): ?>
    <div class="rc-box qw-box">
      <div class="rc t"><div></div></div>
      <div class="content">
        <a href="<?= url_for('form/consult') ?>" class="popup inner" rel="reg"></a>     
        <?= ($id == 0) ? link_to('Задать вопрос<span></span>', '/qa/add', array('class' => 'blue-button', 'id' => 'make-q')).
        link_to('Звонок риэлтора<span></span>', 'form/consult', array('class' => 'blue-button popup inner', 'rel' => 'reg', 'id' => 'r-ring')).
                        '<h2><span></span>Вопросы и ответы</h2>' : '' ?>
        <h2 class="qw-h2-in"><?= format_date($question->created_at, 'd MMMM') ?></h2>
        <?php $date = format_date($question->created_at, 'yyyy-MM-dd')?>
        <?php include_partial('qa/qa_list', array('qa_list' => $qa_dates[$date]))?>
        <?php (($id +1) == $max_days_per_page) ? include_partial('global/posts-paginator', array('pager' => $pager)) : ''?>
      </div><!-- .content -->
      <div class="rc b"><div></div></div>
    </div><!-- .rc-box + .qw-box -->
    <?php if(($id + 1) % 3 == 0 && $max_days_per_page != (int) $id + 1 ):?>
    <div class="index-adv-teaser">
      <div class="wrap closer">
        <?php include_partial('banner/qa_any3rd') ?>
      </div>
    </div>
    <?php endif;?>

  <?php endforeach; ?>

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