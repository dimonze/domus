<?php include_partial('menu/portal-menu', array(
  'post_type'   => 'questionnaire')); ?>
<div id="content">

    <div class="rc-box pools-box">
      <div class="rc t"><div></div></div>
      <div class="content">
        <h2><span class="png24"></span>Актуальные опросы</h2>
        <div class="rc-box-one-col">

          <?php if(count($not_voted_questionnaires) || count($voted_questionnaires)): $i = 0 ;?>
            <?php foreach($not_voted_questionnaires as $q): $i++; ?>
              <?php include_partial('questionnaire/questionnaire_not_voted', array('questionnaire' => $q)) ?>
              <?php if(0 == $i%2): ?><div class="seporator-bl"></div><?php endif ?>
            <?php endforeach ?>
            <?php foreach($voted_questionnaires as $q): $i++; ?>
              <?php include_partial('questionnaire/questionnaire_voted', array('questionnaire' => $q)) ?>
              <?php if(0 == $i%2): ?><div class="seporator-bl"></div><?php endif ?>
            <?php endforeach ?>
          <?php else: ?>
            <p>На данный момент нет ни одного активного опроса</p>
          <?php endif ?>
        </div><!-- .rc-box-one-col -->
      </div><!-- .box-post -->
      <div class="rc b"><div></div></div>
    </div><!-- .rc-box + .news -->

    <?php if(count($pager->getResults())): ?>
    <div class="rc-box events header-bl-box pools-result-box">
      <div class="rc t"><div></div></div>
      <div class="content">
        <h2>Результаты проведенных опросов</h2>
        <?php foreach($pager->getResults() as $q): ?>
          <?php include_partial('questionnaire/questionnaire_voted', array('questionnaire' => $q)) ?>
        <?php endforeach ?>

        <?php include_partial('global/posts-paginator', array('pager' => $pager))?>

      </div>
      <div class="rc b"><div></div></div>
    </div><!-- .header-bl-box -->
    <?php endif ?>
  <?php cached_component('expert_article', 'list', null, $cache_prefix, 1900) ?>
  <?php cached_component('author_article', 'list', array('news' => true), $cache_prefix, 1700) ?>
  <?php include_partial('banner/block5-down-spec') ?>
  </div><!-- #content -->

<div id="aside">
  <?php include_partial('page/aside-head') ?>
  <?php include_partial('banner/block3-right-spec') ?>
  <?php include_component('page', 'aside') ?>
</div><!-- #aside -->
