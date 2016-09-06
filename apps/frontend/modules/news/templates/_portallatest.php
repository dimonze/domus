<?php if (isset($news) && count($news) > 0): ?>
  <div class="rc-box events header-bl-box">
    <div class="rc t"><div></div></div>
    <div class="content">
      <h2>Последние новости портала</h2>
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
    <div class="rc b"><div></div></div>
  </div>
<?php endif ?>