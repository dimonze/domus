<td>
  <ul class="sf_admin_td_actions">
    <?php if ($sf_user->hasCredential('admin-user-actions')): ?>
      <?= $helper->linkToEdit($user, array('params' => array(), 'class_suffix' => 'edit', 'label' => 'Edit')) ?>
    <?php endif ?>
    
    <?php if ($sf_user->hasCredential('admin-user-delete')): ?>
      <?= $helper->linkToDelete($user, array('params' => array(), 'confirm' => 'Are you sure?', 'class_suffix' => 'delete', 'label' => 'Delete')) ?>
    <?php endif ?>
    
    <?php if ($sf_user->hasCredential('admin-user-delete')): ?>
    <div class="promote-count">Рейтинг:&nbsp;<span><?= $user->rating ? $user->rating : '0' ?></span></div><?= link_to('поощрить', 'user/promote?id=' . $user->id, array('class' => 'ajax-promote')) ?>/<?= link_to('наказать', 'user/unpromote?id=' . $user->id, array('class' => 'ajax-promote')) ?>
    <?php endif ?>
    
    <?php if ($user->deleted_at): ?>
      <li class="sf_admin_action_restore">
        <?= link_to('Восстановить', 'user/restore?id=' . $user->id, 'confirm=Вы уверены?') ?>
      </li>
    <?php endif ?>
  </ul>
</td>
