<?php if (count($items)): ?>
  <div class="contentRight_02">
    <div class="cabinetMenuBox ">
      <div class="boxBack_02">
        <h2><?= link_to_if($sf_request->getPathInfo() != '/' . $parent->url, $parent->name, '/'.$parent->url) ?></h2>
      </div>
      <ul class="cabinetMenu padding_11">
        <?php foreach ($items as $page): ?>
          <?php $active = $sf_request->getPathInfo() == '/' . $page->url ?>
          <li class="<?= $active ? 'active' : '' ?>">
            <?= link_to_if(!$active, $page->name, '/'.$page->url) ?>
          </li>
        <?php endforeach ?>
      </ul>
    </div>

    <?php include_partial('banner/block3-right-spec') ?>

  </div>
<?php endif ?>