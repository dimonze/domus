<?php
if (!isset($actions)):
  $actions = array('favourite', 'notify', 'compare', 'map');
endif;

$route_name = (empty($lot->slug) ? 'show_lot': 'show_lot_slug');
?>

<div class="searchResultItem <?= $pos_class ?> <?= $lot->status.' '.$lot->type.'-tile' ?>" latitude="<?= $lot->latitude ?>" longitude="<?= $lot->longitude ?>" rel="<?= $lot->id ?>">
  <?= link_to_if($lot->active, image_tag(lot_image($lot, null, null, '214c140'), array('alt' => lot_title($lot))), prepare_show_lot_url($lot), 'class=resultImg') ?>
  <div class="padding_6">
    <h2>
      <?= link_to_if($lot->active, lot_title($lot, false, $sf_request), prepare_show_lot_url($lot)) ?>
    </h2>
    <?php if($lot->type == 'cottage-sale'): ?>
      <ul>
      <?php $mkad_dist = $lot->getLotInfoField(92); if(!empty($mkad_dist)): ?><li><strong>Расстояние от МКАД:</strong> <?= $mkad_dist ?> км</li><?php endif ?>
      <?php if($area = from_to_output($lot->getLotInfoField(94), $lot->getLotInfoField(95), 'соток')): ?>
        <li><strong>Площадь участков:</strong> <?= $area ?></li>
      <?php endif; ?>
      <?php if($area = from_to_output($lot->getLotInfoField(98), $lot->getLotInfoField(99), 'м<sup>2</sup>')): ?>
        <li><strong>Площадь домов:</strong> <?= $area ?></li>
      <?php endif; ?>
      <?php if($area = from_to_output($lot->getLotInfoField(102), $lot->getLotInfoField(103), 'м<sup>2</sup>')): ?>
        <li><strong>Площадь таунхаусов:</strong> <?= $area ?></li>
      <?php endif; ?>
      </ul>
    <? else: ?>
    <h3><?= $lot->getPriceFormated(isset($currency) ? $currency : 'RUR') ?></h3>
    <ul>
      <?php foreach ($lot->briefArray as $i => $row): ?>
        <li><strong><?= $row[0] ?></strong> <?= isset($row[1]) ? $row[1] : '' ?></li>
      <?php endforeach; ?>
    </ul>
    <? endif; ?>
  </div>
  <?php if($lot->type != 'new_building-sale' && $lot->type != 'cottage-sale'): ?>
    <div class="boxBack_07">
      <div class="date"><?= date('d.m.Y', strtotime($lot->updated_at)) ?></div>
      <?php include_component('lot', 'actions', array('lot' => $lot, 'actions' => $actions)) ?>
    </div>
  <?php endif; ?>
  <?php if (!empty($lot->auto_description)): ?>
    <div class="search-description">
      <p><?= $lot->auto_description ?></p>
    </div>
  <?php endif ?>
</div>
