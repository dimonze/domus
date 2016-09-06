<?php if ($sf_user->hasCredential('moder-access')): ?>
  <div class="sortBox_02">
    <ul class="actionList_02">
      <?php if ($sf_user->hasCredential('moder-actions')): ?>
        <?php if ($lot->editable): ?>
        <li class="edit"><?= link_to('редактировать', 'lot/edit?id='.$lot->id) ?></li>
        <?php endif; ?>
        <?php if ($lot->active): ?>
          <li class="none"><?= link_to('остановить показ', 'lot/restrict?id='.$lot->id) ?></li>
          <li class="none">
            <?= link_to('остановить показ и отправить', 'lot/restrict?id='.$lot->id, array(
              'class' => 'send-mess-rep',
              'name' => 'stop-active-pm',
              'rel' => 'user_id:' . $lot->User->id . ', lot_id:' . $lot->id . ', location:homepage'
            )) ?>
          </li>
        <?php else: ?>
          <li class="none"><?= link_to('активировать', 'lot/setactive?id='.$lot->id) ?></li>
        <?php endif ?>
      <?php endif ?>

      <?php if ($sf_user->hasCredential('moder-delete')): ?>
        <li class="delete"><?= link_to('удалить', 'lot/delete?id='.$lot->id, 'confirm=Вы уверены?') ?></li>
      <?php endif ?>
    </ul>
    <div class="clearBoth"></div>
  </div>
<?php endif ?>