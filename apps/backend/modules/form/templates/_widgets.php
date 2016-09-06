<?php use_javascript('jquery.sortable.js') ?>
<?php use_javascript('global.js') ?>

<div id="widgets">
  <h3>Библиотека виджетов</h3>
  
  <div id="widget-container">
    <div class="block widget additional">
      <span class="label">Дополнительные параметры</span>
    </div>
    <?php foreach ($widgets as $widget): ?>
      <div class="block widget <?php echo $widget->id > 55 ? 'can_delete' : '' ?>" rel="<?php echo $widget->id ?>">
        <span class="actions">
          <?php echo link_to(' ', 'form/widget-delete?id='.$widget->id, 'class=delete') ?>
          <?php echo link_to(' ', 'form/widget-edit?id='.$widget->id, 'class=edit popup rel=reg') ?>
          <a href="#" class="required" title="Обязательное или нет"> </a>
        </span>
        <span class="label">
          <?php if ($widget->comment): ?>
            <?php echo $widget->comment ?>
          <?php else: ?>
            <?php echo $widget->label ?>
          <?php endif ?>
        </span>
        <span class="value"><?php echo $widget->value ?></span>
      </div>
    <?php endforeach ?>
  </div>

  <ul class="sf_admin_actions">
    <li class="sf_admin_action_new"><?php echo link_to('Добавить виджет', 'form/widget-new', 'class=popup rel=reg') ?></li>
  </ul>
</div>