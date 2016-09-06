<?php if ($sf_params->get('offset') && $sf_params->get('hash')): ?>
  <?php use_javascript('search-current.js') ?>

  <div class="searchResultBox" offset="<?= $sf_params->get('offset') ?>" url="<?= url_for('search/get-last?hash='.$sf_params->get('hash')) ?>">
    <div class="boxBack_02">
      <h2>Результаты поиска</h2>
    </div>
    <div class="padding_6">
      <a href="#" class="scrollLeft" title="назад"></a>
      <div class="scrollResult">
        <ul class="resultList"></ul>
      </div>
      <a href="#" class="scrollRight" title="вперед"></a>
      <div class="clearBoth"></div>
      <a href="#" class="link1 link-back">Назад к результатам поиска</a>
    </div>
  </div>
<?php endif ?>