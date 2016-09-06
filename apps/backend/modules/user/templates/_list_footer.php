<?php use_javascript('global.js') ?>
<?php use_javascript('backend.js') ?>
<?php use_helper('DomusForm') ?>
<div>
  <?= label_for('actions', 'Действия над пользователями') ?>
  <?= select_tag('user[actions]', array('Выберите действие', 'Удалить', 'Отправить сообщение', 'Отправить сообщение всем')) ?>
  <?= input_tag('submit', 'OK', array('type' => 'button')) ?>
</div>