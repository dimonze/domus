<div>
<?php $link_title = $lot->address_without_region;
if($lot->type == 'cottage-sale' && ($ct = $lot->getPrepearedLotInfoField(106)) != null) 
  $link_title = $ct.', '.$link_title; ?>
<?= link_to(image_tag(lot_image($lot, 121, 91)) . $link_title, prepare_show_lot_url($lot)) ?>
<?= homepage_lots_info_list($lot)?>
<? if($lot->type != 'cottage-sale'): ?><span><?= $lot->getPriceFormated($sf_request->getParameter('currency', 'RUR'))?></span><?php endif ?>
</div>