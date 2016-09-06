<?php $type = $sf_params->get('current_type') ?>

<?php if (!$type): ?>
  <?= link_to('Список сравнения', '@compare?type=','class=viewCompareList toggle=.compareList') ?>
  <ul class="compareList">
    <?php foreach (sfConfig::get('app_lot_types', array()) as $type => $names): ?>
    <li>
      <?php echo link_to($names['accusative'], '@compare?type='.$type) ?>
    </li>
    <?php endforeach ?>
  </ul>

<?php else: ?>
  <?php $text = 'Список сравнения' ?>
  <?php if ($count = $sf_user->compareCount()): ?>
    <?= link_to(sprintf('Список сравнения <span class="count">(%d)</span>', $count),
                '@compare?type='.$type,
                'class=viewCompareList viewCompareList-active active') ?>
  <?php else: ?>
    <?= link_to('Список сравнения <span class="count" style="display: none"></span>',
                '@compare?type='.$type,
                'class=viewCompareList') ?>
  <?php endif ?>
  

<?php endif ?>