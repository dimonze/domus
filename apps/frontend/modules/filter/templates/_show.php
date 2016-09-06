<?php if (count($filters)): ?>
  <?php foreach ($filters as $filter): ?>
    <?php $filters_array[$filter->id] = $filter->name ?>
  <?php endforeach ?>
  <?= select_tag('moder-filters', options_for_select($filters_array, '', array('include_blank' => true))) ?>
<?php endif ?>
<div class="moder-filters-new">
  <input type="text" name="moder-filters[name]" value="Введите название фильтра" />
  <input type="button" name="moder-filters[ok]" value="Ok" />
</div>
<div class="moder-filters-rename">
  <input type="text" name="moder-filters[rename]" value="" />
  <input type="button" name="moder-filters[rename-ok]" value="Ok" />
</div>
<?=  link_to('Добавить', 'filter/add', array('name' => 'moder-filters[add]')) ?>
<?= link_to('Удалить', 'filter/delete', array('name' => 'moder-filters[delete]')) ?>
<?= link_to('Переименовать', 'filter/rename', array('name' => 'moder-filters[rename]')) ?>