<?php if ($pager->haveToPaginate()): ?>
  <div class="paginator">
    <?= link_to('<span class="arr png24"></span>', sfContext::getInstance()->getRouting()->getCurrentInternalUri() . '&page=' . $pager->getPreviousPage(), array('class' => 'prew-p')) ?>
    <?= link_to('Предыдущая', sfContext::getInstance()->getRouting()->getCurrentInternalUri() . '&page=' . $pager->getPreviousPage(), array('class' => 'prew-p')) ?>
    <span class="paginator-dig">
      <?php foreach ($pager->getLinks() as $page): ?>
        <?php if ($page == $pager->getPage()): ?>
          <?= link_to($page, sfContext::getInstance()->getRouting()->getCurrentInternalUri() . '&page=' . $page) ?>
        <?php else: ?>
          <?= link_to($page, sfContext::getInstance()->getRouting()->getCurrentInternalUri() . '&page=' . $page) ?>
        <?php endif; ?>
      <?php endforeach; ?>
    </span>    
    <?= link_to('Следующая', sfContext::getInstance()->getRouting()->getCurrentInternalUri() . '&page=' . $pager->getNextPage(), array('class' => 'next-p')) ?>
    <?= link_to('<span class="arr png24"></span>', sfContext::getInstance()->getRouting()->getCurrentInternalUri() . '&page=' . $pager->getNextPage(), array('class' => 'next-p')) ?>
    <?= $pager->getPage() ?>/<?= ceil($pager->getNbResults()/$pager->getMaxPerPage()) ?>
  </div>
<?php endif ?>