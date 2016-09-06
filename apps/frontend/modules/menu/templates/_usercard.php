<div class="contentRight_02">
  <div class="cabinetMenuBox ">
    <div class="boxBack_02">
      <h2>Профиль пользователя</h2>
    </div>

    <?php foreach ($menu as $items): ?>
      <?php if (count($items)): ?>
        <ul class="cabinetMenu padding_11">
          <?php foreach ($items as $item): ?>
            <li class="<?= $item['active'] ? 'active' : '' ?>">
              <?= link_to_if(!$item['active'], $item['name'], $item['url']) ?>
            </li>
          <?php endforeach ?>
        </ul>
      <?php endif ?>
    <?php endforeach ?>
  </div>

  <?php include_partial('banner/usermenu') ?>
</div>