<?php if ('received' == $type) { $user = $pm->UserSender;   $direction = 'from'; } ?>
<?php if ('sent' == $type)     { $user = $pm->UserReceiver; $direction = 'to';   } ?>

<span class="pm-subject">
  <?= link_to (strip_tags($pm->subject), 'pm/get-message?id='.$pm->id.'&only-text=1', array('class' => 'inner', 'id' => 'm'.$pm->id)) ?>
</span>

[
  <?php if ($user): ?>
    <span class="pm-<?= $direction ?>">
      <?= link_to($user->name, 'user/card?id=' . $user->id) ?>
    </span>

    <?php if ($sf_user->group_id == 1 || $sf_user->group_id == 2): ?>
      <span class="pm-<?= $direction ?>">
        <a href="mailto:<?= $user->email ?>"><?= $user->email ?></a>
      </span>
      <span class="pm-<?= $direction ?>">
        <?= link_to(
          sprintf('%d объявлен%s', $nb = $user->active_count, ending($nb, 'ие', 'ия', 'ий')),
          'lot/moderate?filter[email]=' . $user->email
        ) ?>
      </span>
    <?php endif ?>

  <?php else: ?>
    <?= $pm->user_name ?>
    <a href="mailto:<?= $pm->user_email ?>"><?= $pm->user_email ?></a>
  <?php endif ?>
]

<span class="pm-date"><?= date('H:i d.m.Y' ,strtotime($pm->sent_at)) ?></span>

<?= link_to('x', 'pm/delete?id=' . $pm->id . '&type=' . $type, 'class=inner delete') ?>