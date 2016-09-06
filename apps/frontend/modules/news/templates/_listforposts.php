<div class="articles-analytics-box">
  <div class="wrap">

    <div class="box analytics">
      <h3><?= link_to('<span class="png24"></span>Новости недвижимости', '@news_by_section?news_section=news-market')?></h3>

      <?php if ($primary_news): ?>
        <div class="item">
          <?php if ($photo = photo($primary_news, 279, 209)): ?>
            <?php if(empty($primary_news->slug)): //TODO Remove when slugs for all posts will be updated ?>
            <?= link_to(image_tag($photo), '@news_show?id=' . $primary_news->id) ?>
            <?php else: ?>
            <?= link_to(image_tag($photo), '@news_show_slug?slug=' . $primary_news->slug.'-'.$primary_news->id) ?>
            <?php endif ?>
          <?php endif ?>

          <h6><?= format_date($primary_news->created_at, 'd MMMM yyyy, HH:mm')?></h6>
          <h4><?php if(empty($primary_news->slug)): //TODO Remove when slugs for all posts will be updated ?>
            <?= link_to($primary_news->title, '@news_show?id=' . $primary_news->id) ?>
            <?php else: ?>
            <?= link_to($primary_news->title, '@news_show_slug?slug=' . $primary_news->slug.'-'.$primary_news->id) ?>
          <?php endif ?></h4>
          <?= $primary_news->lid?>
        </div>
      <?php endif ?>
      <?php if ($news): ?>
        <?php foreach($news as $new): ?>
        <div class="item">
          <h6><?= format_date($new->created_at, 'd MMMM yyyy, HH:mm')?></h6>
          <h4><?php if(empty($new->slug)): //TODO Remove when slugs for all posts will be updated ?>
          <?= link_to($new->title, '@news_show?id=' . $new->id) ?>
          <?php else: ?>
          <?= link_to($new->title, '@news_show_slug?slug=' . $new->slug.'-'.$new->id) ?>
          <?php endif ?></h4>
          <?= $new->lid ?>
        </div>
        <?php endforeach ?>
      <?php endif ?>
      <?= link_to('Все новости', '@news_by_section?news_section=news-market', array('class' => 'bottom-link'))?>
    </div>

  </div>
  <div class="b"></div>
</div>