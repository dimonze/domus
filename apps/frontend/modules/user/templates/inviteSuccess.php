<div class="contentLeft_02">
  <?php include_partial('import/package-import-adv') ?>
  <?php include_component('pm','profilemessages') ?>
  <div class="profileBox">

    <h2>Отправить приглашение</h2>
    <form action="<?= url_for('user/invite') ?>" method="post">
      <?= input_hidden_tag('do', 'send') ?>
      <div class="padding_12">
        <label class="floatLeft">
          Email:
          <?= input_tag('email') ?>
        </label>
        <span class="formButton"><input name="input" type="submit" value="Отправить" /></span>
      </div>
    </form>
    <p>&nbsp;</p>

    <?php if (count($invites)): ?>
      <h2>Активные приглашения</h2>
      <table class="invites">
        <thead>
          <tr>
            <th>Код</th>
            <th>Email</th>
            <th>Дата отправки</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($invites as $invite): ?>
            <tr>
              <td><?= $invite->code ?></td>
              <td><?= $invite->email ?></td>
              <td><?= format_date($invite->created_at, 'dd MMMM yyyy, HH:mm') ?></td>
              <td><?= link_to('Отменить', 'user/invite?do=cancel&code=' . $invite->code . '&email=' . $invite->email) ?></td>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    <?php endif ?>
  </div>
</div>