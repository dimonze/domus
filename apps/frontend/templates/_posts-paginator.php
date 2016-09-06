<?php if (!empty($pager) && $pager->haveToPaginate()): ?>
  <div class="paginator">
    <?php if ($sort_order = $sf_params->get('sort_order')): ?>
      <?php $sort_order = '&sort_order=' . $sort_order ?>
    <?php else: ?>
      <?php $sort_order = '' ?>
    <?php endif ?>
    <?php $prefix = '&page='?>
    <?php if (!empty($query)): ?>
      <?php $query = '&q-search=' . $query ?>
      <?php $prefix = '?page=' ?>
    <?php else: ?>
      <?php $query = '' ?>
    <?php endif ?>
    <?= link_to(
      '<span class="arr png24"></span>',
      sfContext::getInstance()->getRouting()->getCurrentInternalUri(true)
      . $prefix . $pager->getPreviousPage()
      . $sort_order . $query, array('class' => 'prew-p')) ?>
    <?= link_to(
      'Предыдущая',
      sfContext::getInstance()->getRouting()->getCurrentInternalUri(true)
      . $prefix . $pager->getPreviousPage()
      . $sort_order . $query, array('class' => 'prew-p')) ?>
    <span class="paginator-dig">
      <?php foreach ($pager->getLinks() as $page): ?>
        <?php if ($page == $pager->getPage()): ?>
          <span style="color: #76BF1B;"><?= $page ?></span>
        <?php else: ?>
          <?= link_to(
            $page,
            sfContext::getInstance()->getRouting()->getCurrentInternalUri(true)
            . $prefix . $page
            . $sort_order . $query) ?>
        <?php endif; ?>
      <?php endforeach; ?>
    </span>
    <?= link_to(
      'Следующая',
      sfContext::getInstance()->getRouting()->getCurrentInternalUri(true)
      . $prefix . $pager->getNextPage()
      . $sort_order . $query, array('class' => 'next-p')) ?>
    <?= link_to(
      '<span class="arr png24"></span>',
      sfContext::getInstance()->getRouting()->getCurrentInternalUri(true)
      . $prefix . $pager->getNextPage()
      . $sort_order . $query, array('class' => 'next-p')) ?>
  </div>
<?php endif ?>