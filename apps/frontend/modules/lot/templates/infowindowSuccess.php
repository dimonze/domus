<a href="#" class="close floatRight">x</a>
<div class="wrapper">
  <?php foreach ($lots as $lot): ?>
    <div class="item">
      <?php include_component('lot', 'actions', array(
        'lot' => $lot,
        'actions' =>  array('favourite', 'notify', 'compare'))) ?>

      <?= link_to(
        $lot->address1.', '.$lot->address2,
        prepare_show_lot_url($lot),
        'class=link3 rel='.$lot->id) ?>


      <?= link_to(
        image_tag(lot_image($lot)),
        prepare_show_lot_url($lot),
        'class=resultImg rel='.$lot->id) ?>

      <ul class="popupList">
        <li><?= $lot->getPriceFormated($sf_request->getParameter('currency', 'RUR')) ?></li>
        <?php foreach ($lot->briefArray as $i => $row): ?>
          <li>
              <?= $row[0] ?>
              <?= isset($row[1]) ? $row[1] : '' ?>
          </li>
        <?php endforeach ?>
      </ul>
    </div>
  <?php endforeach ?>
</div>