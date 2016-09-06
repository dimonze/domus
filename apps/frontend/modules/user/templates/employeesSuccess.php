<div class="contentLeft_02">
  <?php include_partial('import/package-import-adv') ?>
  <?php include_component('pm','profilemessages') ?>
  <?php if ('company' == $user->type && $user->Employees->count()): ?>
    <div class="profile-ads employees">
      <h2>Сотрудники</h2>
      <div class="ads-box">
        <?php foreach ($user->Employees as $employee): ?>
          <div class="ads-item">
            <div class="pic">
              <a href="<?= url_for('user/employees?mode=kick_out&user_id=' . $employee->id) ?>" class="delete"></a>
              <?= image_tag(photo($employee, 80, 62)) ?>
            </div>
            <?= link_to($employee->name, 'user_card', $employee) ?>
          </div>
        <?php endforeach ?>
      </div>
    </div>
  <? endif ?>
  <?= link_to('Пригласить сотрудников', 'user/invite') ?>
</div>