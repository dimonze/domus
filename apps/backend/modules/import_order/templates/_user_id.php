<?php $user = $form->getObject()->isNew() ? $sf_user : Doctrine::getTable('User')->find($form->getObject()->user_id) ?>
<div class="sf_admin_form_row sf_admin_text sf_admin_form_field_company_name">
  <div>
    <?php if($form->getObject()->isNew() && $sf_user->hasCredential('admin-orders')): 
      $usrs = Doctrine::getTable('User')->findByDql('inactive = ? AND deleted = ? AND (deleted_at IS NULL OR deleted_at = ?)', 
        array(false, false, false), Doctrine_Core::HYDRATE_ARRAY); ?>
      <select name="import_order[user_id]" id="import_order_id">
      <?php foreach ($usrs as $u): ?>
        <option value="<?= $u['id'] ?>"><?= $u['name'] ?></option>
      <? endforeach;?>
      </select>
      <label for="import_order_id">Пользователь</label>
    <? else: ?>
    <input type="hidden" name="import_order[user_id]" value="<?= $form->getObject()->user_id ?>" id="import_order_id">
    <label for="import_order_company_name">Пользователь</label>
    <div class="content">
      <?php if ($user): ?>
        <?= $user->name ?>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
