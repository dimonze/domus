<div class="contentLeft_02">
  <?php if (isset($lots)): ?>
  <div class="viewObjects" style="padding-top: 0px; float: left;">
    <div id="result">
    <?php foreach ($lots as $type => $lots_of_type): ?>
      <?php $types = sfConfig::get('app_lot_types') ?>
      <h3 style="padding: 15px 0px 15px 0px"><?= $types[$type]['plural'] ?></h3>
      <?php if (count($lots_of_type) > 0): ?>
        <?php foreach ($lots_of_type as $lot): ?>
        <div class="searchResultItem active" latitude="<?= $lot->latitude?>" longitude="<?= $lot->longitude ?>" rel="<?= $lot->id ?>">
          <div class="padding_6">
            <h2><?= link_to($lot->address_full, 'lot/show?type=' . $type . '&id=' . $lot->id) ?></h2>
            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="searchResultTable">
              <tbody><tr>
                <td class="sr_01">
                  <?= link_to_if($lot->active, image_tag(lot_image($lot)), prepare_show_lot_url($lot), 'class=resultImg') ?>
                </td>
                <td class="sr_02">
                  <p class="cena_01">
                    <strong><?= $lot->getPriceFormated('RUR') ?></strong>
                  </p>
                </td>
                <td class="sr_03 brief">
                  <?php foreach ($lot->briefArray as $i => $row): ?>
                    <div class="<?= $i == 0 ? 'fontSize_13' : '' ?>">
                      <strong>
                        <?= $row[0] ?>
                      </strong>
                      <?= isset($row[1]) ? $row[1] : '' ?>
                    </div>
                  <?php endforeach ?>
                </td>
              </tr>
            </tbody></table>
          </div>
          <div class="boxBack_07">
            <div class="date"><?= date('d.m.Y', strtotime($lot->updated_at)) ?></div>
            <div class="clearBoth"></div>
          </div>
        </div>
        <?php endforeach ?>
        <?= link_to(
          'Все объявления раздела',
          '@search?type=' . $type . '&rn=' . $nodes_translit[0] . '&l=form'
        )?>
      <?php endif ?>
    <?php endforeach ?>
    </div>
  </div>
  <?php endif ?>
  <div class="textBox">
    <?= $page->content ?>
  </div>
  <?php if ($page->parent_id == 42): ?>
    <?= link_to('назад к словарю', '/slovar_nedvizhimosti') ?>
  <?php endif ?>
</div>
