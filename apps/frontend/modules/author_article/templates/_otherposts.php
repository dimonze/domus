<?php if (isset($articles) && count($articles) > 0): ?>
<div class="rc-box events header-bl-box author-post-other">
  <div class="rc t"><div></div></div>
  <div class="content">
    <h2>Другие посты автора</h2>
    <?php foreach ($articles as $article): ?>
    <div class="item">
      <h5><?= format_date($article->created_at, 'd MMMM yyyy, HH:mm') ?></h5>
      <h4><?php if(empty($article->slug)): //TODO Remove when slugs for all posts will be updated ?>
        <?= link_to($article->title, '@author_article_show?author_id=' . $article->PostAuthor->id . '&id=' . $article->id) ?>
        <?php else: ?>
        <?= link_to($article->title, '@author_article_show_slug?author_id=' . $article->PostAuthor->id . '&slug=' . $article->slug.'-'.$article->id) ?>
      <?php endif ?></h4>
      <?= $article->lid ?>
    </div>
    <?php endforeach ?>
  </div><!-- .content -->
  <div class="rc b"><div></div></div>
</div>
<?php endif ?>