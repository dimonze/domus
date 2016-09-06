<?php if ($sf_user->hasCredential('admin-user')): ?>
  <div class="sortBox_02">
    <ul class="actionList_02">
      <li class="edit">
        <?= link_to('редактировать', '/backend.php/user/'.$user->id.'/edit', 'target=_blank') ?>
      </li>
      <?php if ($sf_user->hasCredential('admin-user-delete')): ?>
        <li class="delete">
          <?= link_to('удалить', '/backend.php/user/'.$user->id, array('method' => 'delete', 'confirm' =>  'Вы уверены, что хотите удалить пользователя?')); ?>
        </li>
      <?php endif ?>
    </ul>
    <div class="clearBoth"></div>
  </div>
<?php endif ?>