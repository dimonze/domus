<?php use_javascript('jquery.columnhover.js') ?>
<?php use_javascript('compare.js') ?>

<div class="compareBox">
  <?php if (count($lots)): ?>

    <div class="mapBox">
      <div id="gmap" style="display: none"></div>
      <div class="mapPopup" style="display: none;"></div>
      <div class="boxBack_01">
        <a href="#" class="collapse collapse-full-text">Развернуть карту</a>
        <div class="clearBoth"></div>
      </div>
    </div>

    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="compareTable">
      <tbody>
        <tr class="tr_01">
          <td class="td_01"></td>
          <?php foreach ($lots as $lot): ?>
            <th class="td_03">
              <?php include_component('lot', 'actions', array('lot' => $lot, 'actions' => array('favourite', 'compare-delete')))?>
            </th>
          <?php endforeach ?>
        </tr>

        <tr class="address">
          <th class="td_02"><strong>Адрес</strong><div class="arrows-compare"><a id="left-arrows-compare" href="#"></a><a id="right-arrows-compare" href="#"></a></div></th>
          <?php foreach ($lots as $lot): ?>
            <td class="td_03" rel="<?= $lot->id ?>" latitude="<?= $lot->latitude ?>" longitude="<?= $lot->longitude ?>">
              <?= link_to(image_tag(lot_image($lot)), prepare_show_lot_url($lot), 'class=compareImg') ?>
              <br />
              <?= link_to($lot->address2, prepare_show_lot_url($lot)) ?>
            </td>
          <?php endforeach ?>
        </tr>

        <tr>
          <th class="td_02"><strong>Цена</strong></th>
          <?php foreach ($lots as $lot): ?>
            <td>
              <strong><?= $lot->getPriceFormated() ?></strong>
            </td>
          <?php endforeach ?>
        </tr>

        <tr>
          <th class="td_02"><strong>Регион</strong></th>
          <?php foreach ($lots as $lot): ?>
            <td>
              <?= $lot->Region->name ?>
            </td>
          <?php endforeach ?>
        </tr>

        <tr>
          <th class="td_02"><strong>Количество дней на&nbsp;сайте</strong></th>
          <?php foreach ($lots as $lot): ?>
            <td>
              <?= $lot->age ?>
            </td>
          <?php endforeach ?>
        </tr>

        <?php foreach ($params as $name => $values): ?>
          <tr>
            <th class="td_02"><strong><?= $name ?></strong></th>
            <?php foreach ($values as $value): ?>
              <td>
                <?= $value[0] ?>
                <?php if (!empty($value[1])): ?>
                  <em><?= $value[1] ?></em>
                <?php endif ?>
              </td>
            <?php endforeach ?>
          </tr>
        <?php endforeach ?>

        <tr>
          <th class="td_02"><strong>Описание</strong></th>
          <?php foreach ($lots as $lot): ?>
            <td class="td_99">
              <div><div><?= $lot->description ?></div></div>
            </td>
          <?php endforeach ?>
        </tr>

        <tr>
          <th class="td_02"><strong>Адрес</strong><div class="arrows-compare"><a id="left-arrows-compare-s" href="#"></a><a id="right-arrows-compare-s" href="#"></a></div></th>
          <?php foreach ($lots as $lot): ?>
            <td class="td_03">
              <?= link_to(image_tag(lot_image($lot)), prepare_show_lot_url($lot), 'class=compareImg') ?>
              <br />
              <?= link_to($lot->address2, prepare_show_lot_url($lot)) ?>
            </td>
          <?php endforeach ?>
        </tr>
      </tbody>
    </table>

  <?php else: ?>
    <p>Объекты в списке отсутствуют</p>
  <?php endif ?>
</div>
