<div class="rc-box events wite-b-news">
  <div class="rc t"><div></div></div>
    <div class="content">
      <h2><?= link_to('<span class="png24"></span>' . $news_section, '@news_by_section?news_section=' . $url) ?></h2>
      <?php if (count($news)): ?>
        <?php foreach($news as $new): ?>
        <div class="item">
          <h6><?= format_date($new->created_at, 'd MMMM yyyy, HH:mm') ?></h6>
          <h4><?php if(empty($new->slug)): //TODO Remove when slugs for all posts will be updated ?>
            <?= link_to($new->title, '@news_show?id='.$new->id) ?>
            <?php else: ?>
            <?= link_to($new->title, '@news_show_slug?slug='.$new->slug.'-'.$new->id) ?>
          <?php endif ?></h4>
          <?= $new->lid ?>
        </div>
        <?php endforeach ?>
      <?php endif ?>
    </div>    
    <?= link_to('Все ' . mb_strtolower($news_section,'UTF-8'), '@news_by_section?news_section=' . $url, array('class' => 'bottom-link')) ?>
  <div class="rc b"><div></div></div>
</div>
