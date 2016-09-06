<?php if (!isset($class)) $class = 'itemAction' ?>

<?php if (count($items)): ?>
  <ul class="<?= $class ?>">
    <?php foreach ($items as $item): ?>
      <li>
        <?= $item['noindex'] ? '<noindex>' : '' ?>
          <?= link_to($item['title'], $item['url'], $item['attr']) ?>
        <?= $item['noindex'] ? '</noindex>' : '' ?>
      </li>
    <?php endforeach ?>
  </ul>
<?php endif ?>