
<?php if('homepage' == $action) include_partial('banner/homepage_first_right') ?>

<?php if($aside['authors']): ?>
  <?php cached_component('author_article', 'list', array('green' => true ), $cache_prefix, 1700) ?>
  <?php if('homepage' == $action) include_partial('banner/homepage_after_authors_columns') ?>
<?php endif ?>

<?php
if('homepage' == $action) {
  include_partial('banner/homepage_1_right');
  if($aside['events']) cached_component('posts', 'eventsonhome', null, $cache_prefix, 1400);
}
?>

<?php if ($aside['blogs']): ?>
  <?php cached_component('blogs', 'postList', null, $cache_prefix, 1) ?>
<?php endif ?>

<?php if('homepage' == $action) include_partial('banner/homepage_2_right') ?>

<?php if($aside['analytics']): ?>
  <div class="articles-analytics-box">
    <div class="wrap">
      <?php cached_component('posts', 'list', array('post_type' => 'analytics'), $cache_prefix, 1200) ?>
    </div>
    <div class="b"></div>
  </div>
<?php endif ?>

<?php if($aside['article']): ?>
  <div class="articles-analytics-box articles-analytics-box-posts">
    <div class="wrap">
      <?php cached_component('posts', 'list', array('post_type' => 'article'), $cache_prefix, 1500) ?>
    </div>
    <div class="b"></div>
  </div>
<?php endif ?>

<?php if($aside['news']): ?>
  <?php cached_component('news', 'listforposts', null, $cache_prefix, 1200) ?>
<?php endif ?>

<?php if (!empty($aside['events_banner'])): ?>
  <?php include_partial('banner/events_partners_right') ?>
<?php endif ?>

<?php if($aside['events'] && 'homepage' != $action): ?>
  <div class="articles-analytics-box articles-analytics-box-posts">
    <div class="wrap">
    <?php cached_component('posts', 'list', array('post_type' => 'events'), $cache_prefix, 1200) ?>
    </div>
    <div class="b"></div>
  </div>
<?php endif ?>


<?php if($aside['qa'] && !sfConfig::get('is_cottage')): ?>
  <?php cached_component('qa', 'list', array('post_type' => 'qa'), $cache_prefix, 1200) ?>
<?php endif ?>

<?php if($aside['questionnaire'] && !sfConfig::get('is_cottage')): ?>
  <?php include_component('questionnaire', 'asideone') ?>
<?php endif ?>
<?php if($aside['experts']): ?>
  <?php include_component('expert_article', 'list') ?>
<?php endif; ?>

