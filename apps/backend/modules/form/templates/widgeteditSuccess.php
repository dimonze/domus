<?php use_helper('DomusForm') ?>

<form action="<?= url_for('form/widget-save') ?>" method="post" class="ajax-validate">
  <fieldset style="margin: 0">
    <legend>
      <span class="prependClose">Редактирование виджета</span>
    </legend>

    <?php echo input_hidden_tag('id', $widget->id) ?>

    <div>
      <label>Название (backend)</label>
      <?php echo input_tag('comment', $widget->comment) ?>
    </div>

    <?php if (in_array($widget->type, array('select', 'multiple', 'radio', 'radiocombo'))): ?>
      <div>
        <label>
          Значения<br />
          <small>Множественный выбор &mdash; <br />по одному значению в строку</small>
        </label>
        <?php echo textarea_tag('value', $widget->value, 'style=width: 178px; height: 7em;') ?>
      </div>
    <?php endif ?>

    <div>
      <label>Название</label>
      <?php echo input_tag('label', $widget->label) ?>
    </div>
    <div>
      <label>Подсказка</label>
      <?php echo input_tag('help', $widget->help) ?>
    </div>
    <div>
      <label>Кол-во баллов</label>
      <?php echo input_tag('rating', $widget->rating) ?>
    </div>
    <div>
      <label>Xml-tag</label>
      <?php echo input_tag('xml_name', $widget->xml_name) ?>
    </div>
    <div>
      <label>Xml-описание</label>
      <?php echo input_tag('xml_desc', $widget->xml_desc) ?>
    </div>

    <div style="height: 45px;">
      <label>&nbsp;</label>
      <input class="popupSubmit send" type="submit" value="Сохранить" />
    </div>
  </fieldset>
</form>