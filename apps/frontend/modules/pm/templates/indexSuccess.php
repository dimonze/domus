<?php use_helper('Escaping') ?>

<?php function show_pager(sfParameterHolder $sf_params, sfPager $pager, $name) { ?>
  <?php if ($pager->haveToPaginate()): ?>
    <?php if ($name == 'received_page'): ?>
      <?php $route_params = 'sent_page=' . $sf_params->get('sent_page', 1) ?>
    <?php elseif ($name == 'sent_page'): ?>
      <?php $route_params = 'received_page=' . $sf_params->get('received_page', 1) ?>
    <?php endif ?>
    <?php $route_params =  (isset($route_params) ? $route_params : '') . sprintf('&%s=', $name) ?>

    <ul class="message-page-nav">
      <?php foreach ($pager->getLinks(10) as $page): ?>
        <li>
          <?= link_to($page, 'pm/index?' . $route_params . $page,
                      'class=inner ' . ($page == $pager->getPage() ? 'current' : '')) ?>
        </li>
      <?php endforeach ?>
    </ul>
  <?php endif ?>
<?php } ?>

<div class="contentLeft_02">
  <?php include_partial('import/package-import-adv') ?>
  <?php include_component('pm','profilemessages') ?>
  <?php if (!$pagers['received']->getResults() && !$pagers['sent']->getResults()): ?>
    <p>У вас нет сообщений.</p>

  <?php else: ?>
    <div class="profileBox">
      <?php foreach (array('received', 'sent') as $type): ?>
        <div id="pm-<?= $type ?>" class="pm-block">
          <?php if ($pagers[$type]->getResults()): ?>
            <h2><?= 'received' == $type ? 'Получены' : 'Отправлены' ?>:</h2>

            <ul class="pms">
              <?php foreach ($pagers[$type]->getResults() as $pm): ?>
                <li class="pm <?= 'received' == $type && !$pm->red ? 'pm-unread' : '' ?>">
                  <?php include_partial('item', array('pm' => $pm, 'type' => $type)) ?>
                </li>
              <?php endforeach ?>
            </ul>
          <?php endif ?>

          <?php show_pager($sf_params, $pagers[$type], $type . '_page') ?>
        </div>
        <br />
      <?php endforeach ?>
    </div>
  <?php endif ?>
</div>