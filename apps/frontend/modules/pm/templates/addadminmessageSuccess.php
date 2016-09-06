<form action="<?= url_for('pm/addadminmessage') ?>" method="post" class="ajax-validate pm">
  <fieldset>
    <legend><span class="prependClose">Новое сообщение</span></legend>

    <div><div>
      <label>Получатель:</label>
      <?php if (!empty($receiver)): ?>
        <?php if (count($receiver) > 1): ?>
          <?php $receiver_ids = ''?>
          <?php $receiver_names = ''?>
          <?php foreach ($receiver as $receiver_user): ?>
            <?php $receiver_names = ($receiver_names != '') ?
              $receiver_names . '; ' . $receiver_user->name : $receiver_user->name ?>
            <?php $receiver_ids = ($receiver_ids != '') ?
              $receiver_ids . '; ' . $receiver_user->id : $receiver_user->id ?>
          <?php endforeach ?>
          <?= input_tag('send_to', $receiver_ids, 'type=hidden') ?>
          <?= input_tag('name', $receiver_names, 'disabled=true') ?>
        <?php elseif (is_string($receiver[0])): ?>
          <?= input_tag('send_to', $receiver[0], 'type=hidden') ?>
          <?= input_tag('name', 'Все пользователи', 'disabled=true') ?>
        <?php else: ?>
          <?= input_tag('send_to', $receiver[0]->id, 'type=hidden') ?>
          <?= input_tag('name', $receiver[0]->name, 'disabled=true') ?>
        <?php endif ?>
      <?php else: ?>
        <?= $form['receiver'] ?>
      <?php endif ?>
    </div></div>
    
    <div><div>
      <label>Важность:</label>
      <?= $form['priority'] ?>
    </div></div>
    
    <?php if ($sf_user->hasCredential('moder-actions')): ?>
    <div>
      <label> Шаблон: </label>
      <?= select_tag('email_themes', array_keys(array_merge(array('' => ' '), $themes))) ?>
      <?php $theme_count = 1; ?>
      <?php foreach ($themes as $theme_body): ?>
        <?php if (isset($lot)): ?>
          <?php if (count($lot) == 1): ?>
            <?php $theme_body['body'] = preg_replace('/{адрес объявления}/', link_to($lot[0]->address_full, 'lot_action', array('id' => $lot[0]->id, 'action' => 'edit'), 'class=address_full') , $theme_body['body']) ?>
          <?php endif ?>
        <?php endif ?>
      <?php if (count($receiver) == 1): ?>
        <?php $theme_body['body'] = preg_replace('/{имя фамилия}/', $receiver[0]->name, $theme_body['body']) ?>
      <?php endif ?>
      <?= input_tag('themes_' . $theme_count, $theme_body['body'], 'type=hidden') ?>
      <?php $theme_count++ ?>
      <?php endforeach ?>
    </div>
    <?php endif ?>
    <div><div>
      <label>Тема:</label>
      <?= $form['subject'] ?>
    </div></div>
    
    <div><div>
      <label>Сообщение:</label>
      <?= $form['message'] ?>
    </div></div>
    <div>
      <?= label_for('email_send', 'Отправить на email')?>
      <?= checkbox_tag('email_send', 1, true) ?>
    </div>
    <div>
      <?= label_for('pm_send', 'Отправить в личные сообщения')?>
      <?= checkbox_tag('pm_send', 1, true) ?>
    </div>
    <div style="height: 50px;">
      <input class="popupSubmit send" type="submit" value="Отправить" />
    </div>
  </fieldset>
</form>
