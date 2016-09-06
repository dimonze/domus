<?php if ($pager['total'] > 1):
  sfContext::getInstance()->getConfiguration()->loadHelpers('Domus');
?>
  <div class="paginator">
    <?php if ($pager['current'] > 1): ?>
    <?= link_to('<span class="arr png24"></span>', preparePostUrlPatternPaginated(sfContext::getInstance()->getRouting()->getCurrentInternalUri(), ($pager['prev'] ? $pager['prev'] : $pager['current']), $sf_request->hasParameter('slug')), array('class' => 'prew-p')) ?>
    <?= link_to('Предыдущая', preparePostUrlPatternPaginated(sfContext::getInstance()->getRouting()->getCurrentInternalUri(), ($pager['prev'] ? $pager['prev'] : $pager['current']), $sf_request->hasParameter('slug')), array('class' => 'prew-p')) ?>    
    <?php endif ?>
    <span class="paginator-dig">
      <?php for ($i=1; $i<=$pager['total']; $i++): ?>
        <?php if($i == $pager['current']): ?>
          <span style="color: #76BF1B;"><?= $i ?></span>
        <?php else: ?>
          <?= link_to($i, preparePostUrlPatternPaginated(sfContext::getInstance()->getRouting()->getCurrentInternalUri(),$i, $sf_request->hasParameter('slug'))) ?>
        <?php endif;?>
      <?php endfor; ?>
    </span>
    <?php if ($pager['current'] < $pager['total']) { ?>
    <?= link_to('Следующая', preparePostUrlPatternPaginated(sfContext::getInstance()->getRouting()->getCurrentInternalUri(), ($pager['next'] ? $pager['next'] : $pager['current']), $sf_request->hasParameter('slug')), array('class' => 'next-p')) ?>
    <?= link_to('<span class="arr png24"></span>', preparePostUrlPatternPaginated(sfContext::getInstance()->getRouting()->getCurrentInternalUri(), ($pager['next'] ? $pager['next'] : $pager['current']), $sf_request->hasParameter('slug')), array('class' => 'next-p')) ?>
    <?php } ?>
  </div>
<?php endif ?>