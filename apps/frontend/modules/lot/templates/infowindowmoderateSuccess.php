<?php $has_actions = $sf_user->hasCredential(array('moder-actions', 'moder-delete'), false) ?>
<a href="#" class="close floatRight">x</a>
<div class="wrapper">
  <?php foreach ($lots as $lot): ?>
    <div class="item">
      <?php if ($has_actions != false): ?>
        <ul class="itemAction">
          <?php if ($sf_user->hasCredential('moder-actions')): ?>
            <li>
              <?= link_to('редактировать', '/user/pm/add?to='.$lot->User->id, 'class="popup send-mess" title="Отправить сообщение" rel=reg') ?>
            </li>
            <li>
              <?= link_to('редактировать', 'lot/edit?id='.$lot->id, 'class=edit title=Редактировать') ?>
            </li>
            <?php if ($lot->active): ?>
              <li>
                <?= link_to('остановить показ', 'lot/restrict?id='.$lot->id, 'class=none title=Остановить показ') ?>
              </li>
              <li>
                <?= link_to('остановить показ и отправить соощение', 'lot/restrict?id=' . $lot->id , 'name="stop-active-pm" class="popup send-mess-rep" title=Остановить показ и отправить сообщение rel="user_id:' . $lot->User->id . ', lot_id:' . $lot->id . '"') ?>
              </li>
            <?php else: ?>
              <li>
                <?= link_to('активировать', 'lot/setactive?id='.$lot->id, 'class=none title=Активировать') ?>
              </li>
            <?php endif ?>
          <?php endif ?>

          <?php if ($sf_user->hasCredential('moder-delete')): ?>
            <li>
              <?= link_to('удалить', 'lot/delete?id='.$lot->id, 'class=delit title=Удалить confirm=Вы уверены?') ?>
            </li>
          <?php endif ?>
        </ul>
      <?php endif ?>
      <div class="item-inner">
        <?= link_to(
          $lot->address1.', '.$lot->address2,
          prepare_show_lot_url($lot),
          'class=link3 rel='.$lot->id) ?>

        <?= link_to(
          image_tag(lot_image($lot)),
          prepare_show_lot_url($lot),
          'class=resultImg rel='.$lot->id) ?>

        <ul class="popupList">
          <li><?= $lot->getPriceFormated($sf_request->getParameter('currency', 'RUR')) ?></li>
          <?php foreach ($lot->briefArray as $i => $row): ?>
            <li>
                <?= $row[0] ?>
                <?= isset($row[1]) ? $row[1] : '' ?>
            </li>
          <?php endforeach ?>
        </ul>
      </div>
    </div>
  <?php endforeach ?>
</div>