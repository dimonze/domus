<form action="<?= url_for('pm/addnologin') ?>" method="post" class="ajax-validate pm">
  <fieldset>
    <legend><span class="prependClose">Новое сообщение</span></legend>
    <?php if (empty($receiver)): ?>
      <div><div>
        <label>Получатель:</label>
          <?= $form['receiver'] ?>
      </div></div>
    <?php else: ?>
      <?= input_tag('send_to', $receiver->id, 'type=hidden') ?>
    <?php endif;?>
    
    <div><div>
      <label>Имя:</label>
      <?= $form['name'] ?>
    </div></div>

    <div><div>
      <label>E-mail:</label>
      <?= $form['email'] ?>
    </div></div>

    <div><div>
      <label>Телефон:</label>
      <?= $form['phone'] ?>
    </div></div>

    <div><div>
      <label>Важность:</label>
      <?= $form['priority'] ?>
    </div></div>

    <div><div>
      <label>Тема:</label>
      <?php          
        if (!empty($subject))
          echo $form['subject']->render(array('disabled' => 'true', 'value' => $subject));
        else
          echo $form['subject'];
      ?>
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
