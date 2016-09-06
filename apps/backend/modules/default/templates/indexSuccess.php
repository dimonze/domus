<?php include_partial('global/assets') ?>

<div id="sf_admin_container">
  <h1>Добро пожаловать в систему администрирования!</h1>

  <ul>
    <?php foreach (sfConfig::get('app_menu', array()) as $i => $item): ?>
      <?php if (empty($item['credential']) || $sf_user->hasCredential($item['credential'])): ?>
        <li style="font-size: 1.6em;">
          <?= link_to($item['text'], $item['route'], 'style=background-image: none; padding: 0') ?>
        </li>
      <?php endif ?>
    <?php endforeach ?>
  </ul>
</div>