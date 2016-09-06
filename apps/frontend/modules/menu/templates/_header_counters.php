<?php if (!sfConfig::get('lot_noindex')): ?>
    <noindex>
<?php endif ?>
  <div class="header_counters">
    <?= sfConfig::get('app_layout_header_counters') ?>
  </div>
<?php if (!sfConfig::get('lot_noindex')): ?>
  </noindex>
<?php endif ?>