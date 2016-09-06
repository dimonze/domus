<div class="contentLeft_02">
  <?php include_component('pm','profilemessages') ?>
  <div class="favoritesObjects">
    <?php include_partial('import/package-import-adv') ?>
    <div class="sortBox_02">
      <form action="<?= url_for('lot/favourite') ?>" method="post" class="favoritesForm">
        <fieldset>
          <div class="floatLeft">
            <?= $filterForm['region_id'] ?>
            <?= $filterForm['type'] ?>
          </div>
          <div class="floatRight">
            <span class="formButton"><input name="input" type="submit" value="Найти" /></span>
          </div>
          <div class="clearBoth"></div>
        </fieldset>
      </form>
    </div>

    <?php if (!$pager->getNbResults()): ?>
      <p>У вас отсутствуют объявления.</p>
    <?php else: ?>

      <?php foreach ($pager->getResults() as $lot): ?>
      <div class="favoriteBox">

        <?php include_partial('lot/item', array('lot' => $lot)) ?>

        <ul class="actionList_02">
          <li><?= link_to('удалить из избранных', 'lot/favourite?id='.$lot->id, 'class=delete') ?></li>
        </ul>
        <div class="clearBoth"></div>
      </div>
      <?php endforeach ?>

    <div class="clearBoth"></div>

    <?php include_partial('global/pagination', array('pager' => $pager)) ?>

    <?php endif ?>
    <?php include_partial('banner/block5-down-spec') ?>
  </div>
</div>