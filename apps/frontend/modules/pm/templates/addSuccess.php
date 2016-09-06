<form action="<?= url_for('pm/add') ?>" method="post" class="ajax-validate pm">
  <fieldset>
    <legend><span class="prependClose">Новое сообщение</span></legend>

    <?php if (empty($receiver)): ?>
      <div><div>
        <label>Получатель:</label>
        <?= $form['receiver'] ?>
      </div></div>
    <?php elseif(!$sf_request->hasParameter('question')): ?>
      <div><div>
        <label>Получатель:</label>
        <?= input_tag('send_to', $receiver->id, 'type=hidden') ?>
        <?= input_tag('name', $receiver->name, 'disabled=true') ?>
      </div></div>
    <?php else: ?>
      <?= input_tag('send_to', $receiver->id, 'type=hidden') ?>
    <?php endif;?>
    <div><div>
      <label>Важность:</label>
      <?= $form['priority'] ?>
    </div></div>
    <div><div>
      <label>Тема:</label>
      <?php if (!empty($subject)): ?>
        <?= $form['subject']->render(array('disabled' => 'true', 'value' => $subject)) ?>
      <?php else: ?>
        <?= $form['subject'] ?>
      <?php endif ?>
    </div></div>

    <div><div>
      <label>Сообщение:</label>
      <?= $form['message'] ?>
    </div></div>

    <div style="height: 50px;">
      <input class="popupSubmit send" type="submit" value="Отправить" />
    </div>
  </fieldset>
</form>
