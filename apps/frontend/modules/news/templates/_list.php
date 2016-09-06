<?php 
use_helper('Tag');
$show_all_link = isset($show_all_link) ? $show_all_link : true;
?>
<div class="rc-box news<?= !empty($three_columns) ? '' : ' realty'?>">
  <div class="content">
    <div class="col-left">
      <?php $name = !empty($name) ? $name : 'Новости недвижимости'; ?>
      <?php if($show_all_link): ?><h3><?= link_to('<span class="png24"></span>' . $name, '@news_by_section?news_section=news-market')?></h3><?php endif; ?>
      <?php if ($primary_news): ?>
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
      <?php endif ?>
    </div>
  <?php if ($news && !empty($three_columns)): ?>
      <div class="col-right news-columns-wrap">
        <div class="news-columns col-0">
            <?php $i = 0; $c = count($news); $pc = ceil($c/3) ?>
            <?php foreach($news as $new): ?>
              <?php if($i++ > 0 && ($i-1)%$pc == 0): ?>
                </div> <div class="news-columns">
              <?php endif ?>
              <div class="item">
                <h6><?= format_date($new->created_at, 'd MMMM yyyy, HH:mm')?></h6>
                <h4><?php if(empty($new->slug)): //TODO Remove when slugs for all posts will be updated ?>
                  <?= link_to($new->title, ($new->post_type == 'news' ? '@news_show?id='.$new->id : '@post_show?id='.$new->id.'&post_type='.$new->post_type), 'rel=nofollow') ?>
                  <?php else: ?>
                  <?= link_to($new->title, ($new->post_type == 'news' ? '@news_show_slug?slug='.$new->slug.'-'.$new->id : '@post_show_slug?slug='.$new->slug.'-'.$new->id.'&post_type='.$new->post_type), 'rel=nofollow') ?>
                <?php endif ?></h4>
                <?= $new->lid ?>
              </div>
            <?php endforeach ?>
          </div>
      </div>
  <?php elseif($news): ?>
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
  <div class="b"></div>
</div>
