<div class="post-actions">
  <a href="#" class="to-print"><span></span>Распечатать</a>
  <?php include_partial('posts/insert_in_blog') ?>
  <div class="insert-jj">
    <a href="http://vkontakte.ru/share.php?url=<?= $url ?>" class="vkont" title="Ссылка Вконтакте" onclick="window.open(this.href, 'ВКонтакте', 'height=400,width=800');return false;"></a>
    <a href="http://twitter.com/home?status=<?= empty($status) ? 'Сейчас читаю' : $status ?> <?= $url ?>" class="twit" title="Ссылка Твиттер" onclick="window.open(this.href, 'Твиттер', 'height=400,width=800');return false;"></a>
    <a href="http://www.facebook.com/sharer/sharer.php?u=<?= $url ?>" class="face" onclick="window.open(this.href, 'Facebook','height=400,width=800');return false;"></a>
  </div>
</div>