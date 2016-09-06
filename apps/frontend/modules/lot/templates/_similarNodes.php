<?php if (!empty($lots)): ?>
<div class="realty-scroller" rel="<?= $type ?>" <?= (!empty($lot)) ? 'id="' . $lot->id . '"' : '' ?>>
  <input type="hidden" id="check_geo" value="<?= (!empty($check_geo)) ? '1' : ''?>" />
  <input type="hidden" id="check_price" value="<?= (!empty($check_price)) ? '1' : ''?>" />
  <h2><?= (!empty($h2)) ? $h2 : 'Похожие новостройки'?></h2>
  <div class="scroller" <?= (!empty($is_search)) ? 'rel="1"' : ''?>>
    <a class="prev prev-disabled" href="#"></a>
    <div class="wrap">
      <div class="cut" page="<?= $page ?>" nb_pages="<?= $nb_pages ?>">
        <?php foreach ($lots as $lot): ?>
        <div>
          <?= link_to(image_tag(lot_image($lot, 121, 91)) . $lot->address_without_region, prepare_show_lot_url($lot)) ?>
          <?= homepage_lots_info_list($lot)?>
          <? if($lot->type != 'cottage-sale'): ?><span><?= $lot->getPriceFormated($sf_request->getParameter('currency', 'RUR'))?></span><?php endif ?>
        </div>
        <?php endforeach ?>
        </div>
      </div>
    <a class="next" href="#"></a>
  </div>
  <div class="clearBoth"></div>
</div>
<?php endif ?>