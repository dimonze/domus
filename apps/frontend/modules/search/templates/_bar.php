<?php if ($type = $sf_params->get('current_type')) $url = url_for('@search?type='.$type) ?>
<?php else $url = url_for('search/index') ?>

<form action="<?= $url ?>" method="post">
  <fieldset>
    <input type="text" class="topSearchInput" name="q" init="населенный пункт / метро / улица / ключевое слово" />
    <button class="topSearchButton" type="submit" value="Найти">Найти</button>
  </fieldset>
</form>