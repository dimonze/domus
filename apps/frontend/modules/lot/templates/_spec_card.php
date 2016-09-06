<?= link_to_if($lot->active, image_tag(lot_image($lot, 128, 85), array('alt' => lot_title($lot), 'class' => 'lot-image')), prepare_show_lot_url($lot)); ?>
<div class="productContainer">
  <div class="productOptionsBox">
    <?php foreach ($lot->LotInfoArray as $id => $group): ?>
      <?php if ($id >= 2): ?>
        <?php break; ?>
      <?php endif ?>
      <?php if (count($group)): ?>
        <ul class="productOptionsList">
          <?php foreach($group as $lotInfo): ?>
            <li>
              <?php if (isset($lotInfo['id']) && in_array($lotInfo['id'], array(68,69))) continue; ?>
              <span class="def"><span><?= $lotInfo['name'] ?>:</span></span> <span class="val" style="display: inline-block;"><span><?= $lotInfo['value'] ?>
              <?php if ($lotInfo['help']): ?>
                <i><?= $lotInfo['help'] ?></i>
              <?php endif ?>
              </span></span>
            </li>
          <?php endforeach ?>
        </ul>
      <?php endif ?>
    <?php endforeach ?>
  </div>
</div>