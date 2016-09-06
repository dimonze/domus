<div class="contentLeft_02">
  <?php include_component('pm','profilemessages') ?>
  <div class="favoritesObjects">
    <?php include_partial('import/package-import-adv') ?>
    <div class="sortBox_02">
      <form action="<?= url_for('lot/my') ?>" method="post" class="favoritesForm">
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
      <p>Вы можете <?= link_to('добавить новое.', 'lot/add') ?></p>
    <?php else: ?>

      <?php foreach ($pager->getResults() as $lot): ?>
      <div class="favoriteBox">

        <?php include_partial('lot/item', array('lot' => $lot, 'actions' => array('favourite', 'compare'), 'status' => 'full')) ?>

        <ul class="actionList_02">
          <?php if($lot->editable): ?>
            <li><?= link_to('редактировать', 'lot/edit?id='.$lot->id, 'class=edit') ?></li>
          <?php endif ?>

          <?php if ($lot->status == 'inactive'): ?>
            <li><?= link_to('возобновить&nbsp;показ', 'lot/setactive?id='.$lot->id, 'class=none') ?></li>
          <?php elseif ($lot->status == 'active'): ?>
            <li><?= link_to('остановить&nbsp;показ', 'lot/setinactive?id='.$lot->id, 'class=none') ?></li>
          <?php endif ?>

          <li><?= link_to('удалить', 'lot/delete?id='.$lot->id, 'class=delete confirm=Вы действительно хотите удалить объявление?') ?></li>

          <?php if ($lot->active): ?>
            <li>
              <div class="insert-jj">
                <?php $url = prepare_show_lot_url($lot) ?>
                <a href="http://vkontakte.ru/share.php?url=<?= $url ?>&image=<?= lot_image($lot, 128, 85); ?>" class="vkont" title="Ссылка Вконтакте" onclick="window.open(this.href, 'ВКонтакте', 'height=400,width=800');return false;"></a>
                <a href="http://twitter.com/home?status=<?= urlencode(lot_title($lot)) ?> <?= $url ?>" class="twit" title="Ссылка Твиттер" onclick="window.open(this.href, 'Твиттер', 'height=400,width=800');return false;"></a>
                <a class="face" href="#" title="Ссылка Facebook" onclick="window.open('http://www.facebook.com/sharer.php?s=100&p[title]=<?= urlencode(lot_title($lot)) ?>&p[url]=<?= $url ?>&p[images][0]=<?= lot_image($lot, 128, 85); ?>', 'Facebook', 'height=400,width=800');return false;"></a>
              </div>
            </li>
          <?php endif ?>
        </ul>
        <div class="clearBoth"></div>
      </div>
      <?php endforeach ?>

    <div class="clearBoth"></div>

    <?php include_partial('global/pagination', array('pager' => $pager)) ?>

    <?php endif ?>
  </div>
</div>