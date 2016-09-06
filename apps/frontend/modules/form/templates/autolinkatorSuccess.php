<?php if (!empty($text) && !empty($links)): ?>
  <div id="post_text_links">
    <textarea name="<?= $post_type ?>" style="display: none;">
      <?= $text ?>
    </textarea>
    <div id="links">
      <h2>Хочу добавить ссылочек:</h2>
      <?php foreach ($links as $link => $word): ?>
      <div id="link">
        <span style="margin-right: 10px;"><?= $word ?></span><?= $link ?><br />
      </div>
      <?php endforeach ?>
      <input type="submit" value="Добавить" name="save_links" />
      <input type="button" value="Отменить" name="discard_links" />
    </div>
  </div>
<?php endif ?>