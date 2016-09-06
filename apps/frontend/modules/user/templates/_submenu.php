<ul class="<?= $class ?>">
  <?php foreach(sfConfig::get('app_usermenu', array()) as $item): ?>
    <?php if (!$item): ?>
      <li class="spacer"></li>

    <?php elseif (!isset($item['credential']) || $sf_user->hasCredential($item['credential'])): ?>
      <?php if (empty($item['user_type']) || $sf_user->type == $item['user_type']): ?>
        <?php $active = $sf_context->getRouting()->getCurrentInternalUri() == $item['url'] ?>
        <?php $active = $active || $sf_request->getParameter('module').'/'.$sf_request->getParameter('action') == $item['url'] ?>
        <li class="<?= $active ? 'active' : '' ?>">
          <?= link_to_if(!$active, $item['name'], $item['url']) ?>

          <?php if ($item['url'] == 'lot/my' && isset($lots)): ?>
            <?php if (!empty($expanded)): ?>
              <span title="Всего"><?= array_sum($lots) ?></span>
              <?php unset($lots['active']) ?>
              <ul>
                <?php foreach ($lots as $status => $nb): ?>
                  <li class="status-<?= $status ?>">
                    <?= Lot::$statuses_plural[$status] ?>
                    <?= $nb ?>
                  </li>
                <?php endforeach ?>
              </ul>

            <?php else: ?>
              (<span title="Всего"><?= array_sum($lots) ?></span><?php unset($lots['active']) ?><?= !count($lots) ? ')' : '' ?>
              <?php $i = 1 ?>
              <?php foreach ($lots as $status => $nb): ?>
                / <span class="status-<?= $status ?>" title="<?= Lot::$statuses_plural[$status] ?>"><?= $nb ?></span><?= $i++ == count($lots) ? ')' : '' ?>
              <?php endforeach ?>
            <?php endif ?>

          <?php elseif ($item['url'] == 'pm/index' && isset($messages)): ?>
            <?php if (!empty($expanded)): ?>
              <?= $messages['total'] ?>
              <?php if (!empty($messages['unread'])): ?>
                <ul class="unread-messages-container">
                  <li>Новые <span class="unread-messages"><?= $messages['unread'] ?></span></li>
                </ul>
              <?php endif ?>


            <?php else: ?>
              (<?= $messages['total'] ?><span class="unread-messages-container"><?php if (!empty($messages['unread'])): ?> / <b title="Новые" class="unread-messages"><?= $messages['unread'] ?></b><?php endif ?></span>)
            <?php endif ?>

          <?php elseif ($item['url'] == 'blogs/my' && isset($blog_posts)):?>
            <?php if (!empty($expanded)): ?>
              <span title="Всего">(<?= $blog_posts.' запис'.ending(count($blog_posts), 'ь', 'и', 'ей') ?>)</span>
            <?php endif ?>
          <?php elseif ($item['url'] == 'lot/favourite' && isset ($favourite)): ?>
            <?php if (!empty($expanded)): ?>
              <span title="Всего"><?php echo $favourite ?></span>
              <?php unset($favourite) ?>
            <?php else: ?>
              (<span title="Всего"><?php echo $favourite ?></span>)
            <?php endif ?>
          <?php endif ?>

        </li>
      <?php endif ?>
    <?php endif ?>
  <?php endforeach ?>
  <li><?= link_to('Выход', 'user/logout') ?></li>
</ul>
