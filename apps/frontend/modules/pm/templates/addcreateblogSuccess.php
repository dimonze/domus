<form action="<?= url_for('pm/addcreateblog') ?>" method="post" class="ajax-validate pm">
  <fieldset>
    <legend><span class="prependClose">Запрос на создание блога</span></legend>

    <div><div>
      <label>Получатель:</label>
      <?php if (!empty($receiver)): ?>
        <?= input_tag('send_to', $receiver->id, 'type=hidden') ?>
        <?= input_tag('name', $receiver->name, 'disabled=true') ?>
      <?php else: ?>
        <?= $form['receiver'] ?>
      <?php endif ?>
    </div></div>

    <div><div>
      <label>Название блога:</label>
      <?= $form['blog_name'] ?>
    </div></div>

    <div><div>
      <label>Url блога:</label>
      <?= $form['blog_url'] ?>
    </div></div>

    <div style="height: 50px;">
      <input class="popupSubmit send" type="submit" value="Отправить" />
    </div>
  </fieldset>
</form>
