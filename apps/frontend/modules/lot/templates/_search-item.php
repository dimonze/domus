<?= link_to_if($lot->active, image_tag(lot_image($lot, 111, 83), 'alt= class=searchImg'), prepare_show_lot_url($lot), 'title='.$lot->address1.' '.$lot->address2) ?>
<ul class="resultOptions">
  <li><?= $lot->getPriceFormated(isset($currency) ? $currency : 'RUR') ?></li>
  <?php foreach ($lot->briefArray as $i => $row): ?>
  <li>
    <strong><?= $row[0] ?></strong>
    <?= isset($row[1]) ? $row[1] : '' ?>
  </li>
  <?php endforeach ?>
</ul>

