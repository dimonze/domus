<div class="rc-box expert-opinion">
  <div class="rc t"><div></div></div>
  <div class="content">

    <h3><?= link_to('<span class="png24"></span>Экспертные мнения', '@expert_article')?></h3>
    <?php if ($articles): ?>
      <?php $i = 0; ?>
      <?php foreach ($articles as $article): ?>
        <?php $i++ ?>
        <div class="bubble-wrp">
          <?php if ($photo = photo($article->PostAuthor, 50, 50)): ?>
            <?php if(empty($article->slug)): //TODO Remove when slugs for all posts will be updated ?>
              <?= link_to(image_tag($photo, 'class=bubble-foto'), '@expert_article_show?id=' . $article->id . '&author_id=' . $article->author_id) ?>
            <?php else: ?>
              <?= link_to(image_tag($photo, 'class=bubble-foto'), '@expert_article_show_slug?slug=' . $article->slug.'-'.$article->id . '&author_id=' . $article->author_id) ?>
            <?php endif ?>
          <?php endif ?>

          <div class="bubble3">
            <div class="tail-l png24"></div>
            <div class="tl bbl-corn png24"></div>
            <div class="tr bbl-corn png24"></div>
            <div class="bubble3-content">
              <h5><?= $article->PostAuthor->name ?>, <strong><?= $article->PostAuthor->post ?>, <?= $article->PostAuthor->company ?></strong></h5>
              <h4><?php if(empty($article->slug)): //TODO Remove when slugs for all posts will be updated ?>
                <?= link_to($article->title, '@expert_article_show?author_id=' . $article->author_id . '&id=' . $article->id) ?>
                <?php else: ?>
                <?= link_to($article->title, '@expert_article_show_slug?author_id=' . $article->author_id . '&slug=' . $article->slug.'-'.$article->id) ?>
              <?php endif ?></h4>
              <?= ('homepage' == $sf_context->getActionName() && $i != 1) ? '' : $article->lid ?>
            </div>
            <div class="bl bbl-corn png24"></div>
            <div class="br bbl-corn png24"></div>
          </div>
        </div>
      <?php endforeach ?>
    <?php endif ?>
  </div>
  <?= link_to('Все экспертные мнения', '@expert_article', array('class' => 'bottom-link'))?>
  <div class="rc b"><div></div></div>
</div>
