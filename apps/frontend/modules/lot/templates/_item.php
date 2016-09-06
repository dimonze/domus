<?php
if (!isset($actions)):
  $actions = array('favourite', 'notify', 'compare', 'map');
endif;
?>

<div class="searchResultItem buildingView <?= $lot->status?>" latitude="<?= $lot->latitude ?>" longitude="<?= $lot->longitude ?>" rel="<?= $lot->id ?>">
  <div class="padding_6">
    <h2>
      <?= link_to_if($lot->active, lot_title($lot, false, $sf_request), prepare_show_lot_url($lot)) ?>
    </h2>
    
    <?php if ($lot->type == 'new_building-sale'): ?>
      <?= link_to_if($lot->active, image_tag(lot_image($lot, 128, 85), array('alt' => lot_title($lot))), prepare_show_lot_url($lot), 'class=resultImg') ?>
      <h3><strong>Цена:</strong> <?= $lot->getPriceFormated(isset($currency) ? $currency : 'RUR') ?></h3>
      <?php if($area = from_to_output($lot->getLotInfoField(72), $lot->getLotInfoField(73), 'м<sup>2</sup>')): ?>
        <h3><strong>Площадь:</strong> <?= $area ?></h3>
      <?php endif; ?>
      <ul>
        <?php foreach ($lot->briefArray as $i => $row): ?>
          <?php if(!($area && $row[0] == 'Площадь:')): ?>
            <li><strong><?= $row[0] ?></strong> <?= isset($row[1]) ? $row[1] : '' ?></li>
          <?php endif ?>
        <?php endforeach; ?>
      </ul>
    <?php elseif($lot->type == 'cottage-sale'): ?>
      <?= link_to_if($lot->active, image_tag(lot_image($lot, 128, 85), array('alt' => lot_title($lot))), prepare_show_lot_url($lot), 'class=resultImg') ?>
      <?php $mkad_dist = $lot->getLotInfoField(92); if(!empty($mkad_dist)): ?><h3><strong>Расстояние от МКАД:</strong> <?= $mkad_dist ?> км</h3><?php endif ?>
      <?php if($area = from_to_output($lot->getLotInfoField(94), $lot->getLotInfoField(95), 'соток')): ?>
        <h3><strong>Площадь участков:</strong> <?= $area ?></h3>
      <?php endif; ?>
      <?php if($area = from_to_output($lot->getLotInfoField(98), $lot->getLotInfoField(99), 'м<sup>2</sup>')): ?>
        <h3><strong>Площадь домов:</strong> <?= $area ?></h3>
      <?php endif; ?>
      <?php if($area = from_to_output($lot->getLotInfoField(102), $lot->getLotInfoField(103), 'м<sup>2</sup>')): ?>
        <h3><strong>Площадь таунхаусов:</strong> <?= $area ?></h3>
      <?php endif; ?>
    <?php else: ?>
      <table width="100%" border="0" cellspacing="0" cellpadding="0" class="searchResultTable">
        <tr>
          <td class="sr_01">
            <?= link_to_if($lot->active, image_tag(lot_image($lot, 128, 85), array('alt' => lot_title($lot))), prepare_show_lot_url($lot), 'class=resultImg') ?>
          </td>
          <td class="sr_02">
            <p class="cena_01">
              <strong>
                <?php $currency_type = $sf_params->get('currency_type') ?>
                <?= $lot->getPriceFormated(
                    isset($currency) ? $currency : 'RUR',
                    isset($currency_type) ? $currency_type : false
                  )
                ?>
              </strong>
              <?= $lot->getPriceHelp(isset($currency_type) ? $currency_type : false) ?>
            </p>
            <div class="date"><?= date('d.m.Y', strtotime($lot->updated_at)) ?></div>
            <div class="search_actions">
              <?php include_component('lot', 'actions', array('lot' => $lot, 'actions' => $actions)) ?>
            </div>
          </td>

          <td class="sr_03 brief">
            <?php foreach ($lot->briefArray as $i => $row): ?>
              <div class="<?= $i == 0 ? 'fontSize_13' : '' ?>">
                <strong>
                  <?= $row[0] ?>
                </strong>
                <?= isset($row[1]) ? ($row[0] == 'Тип:' && mb_strpos($row[1], ',', null, 'utf-8') !== false ? 'Много&shy;функциональный' : $row[1]) : '' ?>
              </div>
            <?php endforeach ?>
            <h3>
              <?php if (!$lot->User->is_inner): ?>
                <?php if (!empty($status) && $status == 'full'): ?>
                  <span class="status-<?= $lot->status ?>">
                    <?php if ($lot->status == 'restricted'): ?>
                      <?php //no css for form, comment ?>
                      <?php if (null != $lot->moderator_message): ?>
                        <?php echo   $lot->status_text . '&nbsp;' . link_to ('по причине', 'pm/get-message?id='.$lot->moderator_message, 'class=inner popup rel=notify title="'.$lot->status_text.'"') ?>
                      <?php else: ?>
                        <?= $lot->status_text ?>
                      <?php endif; ?>
                    <?php else: ?>
                      <?= $lot->status_text ?>
                    <?php endif ?>
                  </span>
                <?php else: ?>
                  <?php if ($lot->type != 'new_building-sale'): ?>
                    <?= $lot->human_status ?>
                  <?php endif ?>
                <?php endif ?>
              <?php endif ?>
            </h3>
            <div class="clearBoth"></div>
          </td>
        </tr>
      </table>
      <?php if (!empty($lot->auto_description)): ?>
        <div class="search-description">
          <p>
            <? $desc = $lot->separated_auto_description ?>
            <span class="opened"><?= $desc['first'] ?></span>
            <?php if($desc['second']): ?>
              <span class="showClosedDesc action_11">&nbsp;</span>
              <span class="closed"><?= $desc['second'] ?></span>
            <?php endif; ?>
          </p>
        </div>
      <?php endif ?>
    <?php endif; ?>
  </div>
</div>
