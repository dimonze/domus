<div class="contentLeft_02">

  <?php include_partial('user/moderator-tools', array('user' => $user)) ?>

  <div class="profileBox">
    <?php if ($user->type == 'company'): ?>
      <h2>Информация о компании</h2>
    <?php else: ?>
      <h2>Персональные данные</h2>
    <?php endif ?>
    <div class="userpic">
      <div class="image"><?= image_tag(photo($user)) ?></div>

      <?php if ($user->rating): ?>
        Рейтинг <strong><?= (int) $user->rating ?></strong>
      <?php endif ?>
    </div>

    <table>
      <tr>
        <th><?= ($user->is_partner) ? '' : 'Телефон' ?></th>
        <td>
          <span class="tel"><?= ($user->is_partner) ? '' : $user->phone ?></span>
          <?php if ($sf_user->isAuthenticated()): ?>
            <?= link_to('<span>Написать сообщение</span>',
                        'pm/add?to=' . $user->id,
                        'class=popup formButton rel=reg') ?>
          <?php else: ?>
            <?= link_to('<span>Написать сообщение</span>',
                      'pm/addnologin?to=' . $user->id,
                      'class=popup formButton rel=reg') ?>
          <?php endif ?>
        </td>
      </tr>

      <?php if ($user->type == 'employee'): ?>
        <tr>
          <th>Компания:</th>
          <td><?= link_to($user->company_name, 'user_card', $user->Employer) ?></td>
        </tr>
      <?php elseif ($user->type == 'company'): ?>
        <tr>
          <th>Контакное лицо:</th>
          <td><?= $user->name ?></td>
        </tr>
      <?php endif ?>

      <?php foreach ($user->Info->field_names as $key => $name): ?>
        <?php if ($user->Info->$key): ?>
          <tr>
            <th><?= $name ?>:</th>
            <td>
              <?php if ($key == 'site'): ?>
                <noindex><?= link_to($user->Info->site, $user->Info->site_link, 'target=_blank') ?></noindex>
              <?php else: ?>
                <?= $user->Info->$key ?>
              <?php endif ?>
            </td>
          </tr>
        <?php endif ?>
      <?php endforeach ?>
    </table>
  </div>

  <?php include_partial('user/moderator-tools', array('user' => $user)) ?>
<?php if(count($user->Employees) && 'company' == $user->type): ?>
          <div class="profile-ads">
          <h2>
            Сотрудники
          </h2>
          <div class="ads-box">
            <?php foreach ($user->Employees as $employee): ?>
              <div class="ads-item">
                <div class="pic">
                  <?= image_tag(photo($employee, 80, 62)) ?>
                </div>
                <?= link_to($employee->name, 'user_card', $employee) ?>
              </div>
            <?php endforeach ?>
          </div>
        </div>
  <? endif; ?>


    <?php foreach ($lots as $type => $data): ?>
      <?php if ($data['nb_lots'] > 0): ?>
        <div class="profile-ads">
          <h2>
            <?= link_to_if($data['nb_lots'] > 5, $data['name'], '@user_lots?id=' . $user->id . '&type=' . $type) ?>
            <em><?= $data['nb_lots'] ?></em>
          </h2>
          <div class="ads-box">
            <?php foreach ($data['lots'] as $i => $lot): ?>
              <div class="ads-item <?= $i == count($data['lots']) - 1 ? 'last' : '' ?>">
                <div class="pic">
                  <?= image_tag(lot_image($lot)) ?>
                </div>
                <?= link_to($lot->address1, 'show_lot', $lot) ?>
                <span><?= $lot->getPriceFormated('RUR') ?></span>
                <p><?= implode('<br /> ', $lot->briefArrayFormated) ?></p>
              </div>
            <?php endforeach ?>
          </div>
        </div>
      <?php endif ?>
    <?php endforeach ?>
</div>