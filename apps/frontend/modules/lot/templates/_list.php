<?php if (!empty($lots)): ?>
<script type="text/javascript"><!--// <![CDATA[
  if(typeof(window.scroller_params) == 'undefined') window.scroller_params = {};
  <?php if(!empty($params)): ?>window.scroller_params['<?= md5(json_encode($params)) ?>'] = <?= json_encode($params); ?><?php endif ?>
// ]]> --></script>
<div class="realty-scroller" rel="<?= $type ?>" <?= (!empty($lot)) ? 'id="' . $lot->id . '"' : '' ?><?= empty($params) ? '' : ' data-params="'. md5(json_encode($params)).'"' ?>>
  <h2>
    <?=
      link_to(
        isset($name)  ? $name : $types[$type]['name'],
        isset($route) ? $route : '@search?type=' . Lot::getRoutingType($type)
      )
    ?>
  </h2>
  <div class="scroller" <?= ($is_search) ? 'rel="1"' : ''?>>
    <a class="prev prev-disabled" href="#"></a>
    <div class="wrap">
      <div class="cut" page="<?= $page ?>" nb_pages="<?= $nb_pages ?>">
        <?php foreach ($lots as $lot): 
          $link_title = $lot->address_without_region;
          if($lot->type == 'cottage-sale' && ($ct = $lot->getPrepearedLotInfoField(106)) != null) 
            $link_title = $ct.', '.$link_title;
        ?>
        <div>
          <?= link_to(image_tag(lot_image($lot, 121, 91)) . $link_title, prepare_show_lot_url($lot)) ?>
          <?= homepage_lots_info_list($lot)?>
          <? if($lot->type != 'cottage-sale'): ?><span><?= $lot->getPriceFormated($sf_request->getParameter('currency', 'RUR'))?></span><?php endif ?>
        </div>
        <?php endforeach ?>
        </div>
      </div>
    <a class="next" href="#"></a>
  </div>
  <div class="clearBoth"></div>
  <?php if ($is_search): ?>
    <a href="#" class="link1 link-back">Назад к результатам поиска</a>
  <?php endif ?>
</div>
<?php endif ?>