<?php if (isset($news) && count($news) > 0): ?>
  <?php if (!($theme_count%2)): ?>
    <div class="rc-box events header-bl-box">
  <?php else: ?>
    <div class="rc-box events header-bl-box header-oth-box">
  <?php endif ?>
  <div class="rc t"><div></div></div> 
    <div class="content">
      <?php $translit_tbl = DomusSearchRoute::$translit_table ?>
      <?php $theme_url = str_replace(array_keys($translit_tbl), array_values($translit_tbl), $theme)?>
      <?php $created_at = (isset($created_at)) ? $created_at : date('Y-m-d')?>
      <h2><?= link_to($theme, '@news_by_theme?news_section=' . $news_section . '&theme=' . $theme_url . '&created_at=' . $created_at) ?></h2>
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
