<div class="rc-box news themes authors-blue blue-box">
  <div class="rc t"><div></div></div>
  <div class="content">
    <div class="col-left">
      <h2><span class="png24"></span>Эксперты</h2>
      <?php if (!isset($current_author_id)): ?>
        <?php $current_author_id = null ?>
      <?php endif ?>
      <?php if ($authors): ?>
        <ul>
          <?php foreach ($authors as $author): ?>
            <?php if ($current_author_id == $author->id): ?>
              <li><strong><?= link_to($author->name, '@expert_article_show_author?author_id=' . $author->id)?></strong></li>
            <?php else: ?>
              <li><?= link_to($author->name, '@expert_article_show_author?author_id=' . $author->id)?></li>
            <?php endif ?>
          <?php endforeach ?>
        </ul>
      <?php endif ?>
    </div>
  </div>
  <div class="rc b"><div></div></div>
</div>