<?php if (isset($pager)): ?>
  <div class="rc-box events header-bl-box">
    <div class="rc t"><div></div></div>
    <div class="content">
      <h2>Последние новости портала</h2>
        <?php $i = 1; ?>
        <?php foreach($pager->getResults() as $new): ?>
        <div class="item">
          <h6><?= format_date($new->created_at, 'd MMMM yyyy, HH:mm')?></h6>
          <h4><?php if(empty($new->slug)): //TODO Remove when slugs for all posts will be updated ?>
            <?= link_to($new->title, '@news_show?id='.$new->id) ?>
            <?php else: ?>
            <?= link_to($new->title, '@news_show_slug?slug='.$new->slug.'-'.$new->id) ?>
          <?php endif ?></h4>
          <?= $new->lid ?>
          
          <?php if ($i == 5): ?>
            <div class="banner-first-last">
              <?php include_partial('banner/news_first_last'); ?>
            </div>
          <?php endif; ?>
        </div>
          <?php $i++; ?>
        <?php endforeach ?>
      <?php include_partial('paginator', array('pager' => $pager))?>
    </div>
    <div class="rc b"><div></div></div>    
  </div>  
<?php endif ?>