<div class="content">
  <h2><?= $theme ?></h2>
  <?php if (count($articles) > 0): ?>
    <?php foreach ($articles as $article): ?>
      <div class="item">
        <?= image_tag(photo($article, 193, 128)) ?>
        <h6><?= format_date($article->created_at, 'd MMMM yyyy, HH:mm')?></h6>
        <h4><?= link_to($article->title, '@author_article_show?id=' . $article->id . '&author_id=' . $article->author_id)?></h4>
        <?= $article->lid ?>
      </div>
    <?php endforeach ?>
  <?php endif ?>
</div>