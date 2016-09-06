<?php include_partial('menu/portal-menu', array(
  'post_type'     => 'news',
  'news_section'  => $news_section,
  'theme' => $post_theme))?>
<div id="content">
  <div class="rc-box events header-bl-box themes-main-box">
    <div class="rc t"><div></div></div>
    <div class="content">
      <h2><?= $post_theme->title?></h2>
      <?php if (count($news) > 0): ?>
        <?php $last_id = count($news) ?>
        <?php foreach ($news as $id => $new): ?>
          <?php if ($last_id == $id): ?>
            <div class="item last-item">
          <?php else: ?>
            <div class="item">
          <?php endif ?>
          <h6><?= format_date($new->created_at, 'd MMMM yyyy, HH:mm')?></h6>
          <h4><?php if(empty($new->slug)): //TODO Remove when slugs for all posts will be updated ?>
            <?= link_to($new->title, '@news_show?id='.$new->id) ?>
            <?php else: ?>
            <?= link_to($new->title, '@news_show_slug?slug='.$new->slug.'-'.$new->id) ?>
          <?php endif ?></h4>
          <?= $new->lid ?>
        </div>
        <?php endforeach ?>
      <?php else: ?>
        <span style="padding: 10px 0px 10px 0px; margin-top: 20px;">Нет новостей на  <?= format_date($created_at, 'd MMMM yyyy')?></span>
      <?php endif ?>
      </div>
    <div class="rc b"><div></div></div>
  </div>
  <div class="rc-box rc-box-grey rc-days-box">
    <div class="rc t"><div></div></div>
    <div class="content">

      <div class="days-box">
        <?php foreach($days as $v): ?>
          <?php if($v > $real_day) continue; ?>
          <?php $a = link_to($v,
                  sprintf('@news_by_theme?news_section=%s&theme=%s&created_at=%s-%s-%s',
                          $news_section,
                          $theme,
                          $current_year,
                          $current_month,
                          $v > 9 ? $v : '0'.$v
                  ))
          ?>
        <?php if ($v == $current_day): ?>
            <span class="days-active"><?php echo $v ?></span>
          <?php elseif (isset($nb_items_per_day[$v])): ?>
            <span><?php echo $a ?></span>
          <?php else: ?>
            <span><?php echo $v ?></span>
          <?php endif ?>
          <?php endforeach ?>
        <div class="select-box">
          <ul class="select-box-months months-hidden">
            <?php foreach($months as $i => $v): ?>
              <?php $a = link_to($v, sprintf('@news_by_theme?news_section=%s&theme=%s&created_at=%s-%s-%s',
                                             $news_section,
                                             $theme,
                                             $current_year,
                                             $i > 9 ? $i : '0'.$i,
                                             $current_day
                  )) ?>

              <?php if ($i == $current_month): ?>
                <li class="active-month png24"><?php echo $a ?></li>
              <?php else: ?>
                <li><?php echo $a ?></li>
              <?php endif ?>

            <?php endforeach ?>
          </ul>
          <ul class="select-box-years years-hidden">
            <?php foreach($years as $v): ?>
              <?php $a = link_to($v, sprintf('@news_by_theme?news_section=%s&theme=%s&created_at=%s-%s-%s',
                                             $news_section,
                                             $theme,
                                             $v,
                                             $current_month,
                                             $current_day
                  )) ?>

              <?php if ($v == $current_year): ?>
                <li class="active-year png24"><?php echo $a ?></li>
              <?php else: ?>
                <li><?php echo $a ?></li>
              <?php endif ?>

            <?php endforeach ?>
            <li class="box-years-bottom"></li>
          </ul>
        </div>
      </div><!-- .days-box -->

    </div><!-- .content -->
    <div class="rc b"><div></div></div>
  </div>
  <?php include_partial('banner/post_themes') ?>
  <?php cached_component('expert_article', 'list', null, $cache_prefix, 1900) ?>
  <?php include_partial('banner/post_themes') ?>
  <?php cached_component('author_article', 'list', array('news' => true), $cache_prefix, 1700) ?>
  <?php include_partial('banner/block5-down-spec') ?>
</div>

<div id="aside">
  <?php include_partial('page/aside-head')?>
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
  <?php include_component('page', 'aside'); ?>
</div>