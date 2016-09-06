<div class="contentLeft_02">
  <?php include_partial('import/package-import-adv') ?>
  <?php include_component('pm','profilemessages') ?>
  <div class="profileBox">
    <h2>Персональные данные</h2>

    <div class="userpic">
      <div class="image"><?= image_tag(photo($sf_user)) ?></div>

      <?php if ($sf_user->rating): ?>
        Рейтинг <strong><?= (int) $sf_user->rating ?></strong>
      <?php endif ?>
    </div>

    <div class="pers-new">
      <table>
        <colgroup span="2">
          <col class="left-col" />
          <col class="right-col" />
        </colgroup>
        <tr>
          <td><strong>Имя:</strong></td>
          <td><?= $sf_user->name ?></td>
        </tr>

        <?php if ($sf_user->company_name): ?>
        <tr>
          <td><strong>Компания:</strong></td>
          <td><?= $sf_user->company_name ?></td>
        </tr>
        <?php endif ?>

        <tr>
          <td><strong>Email (логин):</strong></td>
          <td><?= $sf_user->email ?>
        </tr>
        <tr>
          <td><strong>Телефон:</strong></td>
          <td><?= $sf_user->phone ?>
        </tr>

        <?php foreach ($sf_user->Info->field_names as $key => $name): ?>
          <tr>
            <td><?= $name ?>:</td>
            <td>
              <?php if ($sf_user->Info->$key): ?>
                <?php if ($key == 'site'): ?>
                  <?= link_to($sf_user->Info->site, $sf_user->Info->site_link, 'target=_blank') ?>
                <?php else: ?>
                  <?= $sf_user->Info->$key ?>
                <?php endif ?>
              <?php else: ?>
                <i style="color: gray">не заполнено</i>
              <?php endif ?>
            </td>
          </tr>
        <?php endforeach ?>

        <tr>
          <td colspan="2">
            <?= link_to('Редактировать данные', 'user/profile?edit=1') ?>
          </td>
        </tr>

      </table>
    </div>
  </div>
  <?php include_partial('profile_settings') ?>
  <?php include_partial('blog') ?>

  <div class="profileBox">
    <h2>Ответы на вопросы</h2>
    <h3>
      Если у вас возникли вопросы -
      <?= link_to('напишите администратору', 'pm/add?moderator=1', 'class=inner popup rel=reg') ?>
    </h3>
  </div>

  <?php include_partial('deactivate') ?>
</div>