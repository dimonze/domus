<div class="contentLeft_02">
  <div class="profile-ads ads-paginated">
    <h2><?= $name ?></h2>
    <div class="ads-box">
      <?php $lots = $pager->getResults() ?>
      <?php foreach ($lots as $i => $lot): ?>
        <div class="ads-item <?= !(($i + 1) % 5) || $i == count($lots) - 1 ? 'last' : '' ?>">
          <div class="pic">
            <?= image_tag(lot_image($lot)) ?>
          </div>
          <?= link_to($lot->address1, 'show_lot', $lot) ?>
          <span><?= $lot->getPriceFormated('RUR') ?></span>
          <p><?= implode('<br /> ', $lot->briefArrayFormated) ?></p>
        </div>
      <?php endforeach ?>
    </div>
  </div>

  <?php include_partial('global/pagination', array(
                          'pager' => $pager,
                          'route' => '@user_lots',
                          'route_params' => array(
                            'id'   => $sf_params->get('id'),
                            'type' => $sf_params->get('type'),
                          ))) ?>
</div>