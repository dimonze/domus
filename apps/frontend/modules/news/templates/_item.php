<div class="searchResultItem <?= $news->status?>" rel="<?= $news->id ?>">
  <div class="padding_6">
    <h2>
      <?php if(empty($news->slug)): //TODO Remove when slugs for all posts will be updated ?>
      <?= link_to_if($news->status == 'publish', $news->title, url_for('news/show?id=' . $news->id, $news)) ?>
      <?php else: ?>
      <?= link_to_if($news->status == 'publish', $news->title, url_for('news/show?slug=' . $news->slug.'-'.$news->id, $news)) ?>
      <?php endif ?>
    </h2>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="searchResultTable">
      <tr>
        <td class="sr_02">
          <?= image_tag(photo($news, 100, 80)) ?>
        </td>
        <td class="sr_03 brief">
          <?= $news->news_lid ?>
        </td>
      </tr>
    </table>
  </div>
  <div class="boxBack_07">
    <div class="date"><?= date('d.m.Y H:i', strtotime($news->created_at)) ?></div>
    <h3><?= $news->status_text ?></h3>
    <div class="clearBoth"></div>
  </div>
</div>