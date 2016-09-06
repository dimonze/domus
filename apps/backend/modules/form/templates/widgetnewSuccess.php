<?php use_helper('DomusForm') ?>

<form action="<?= url_for('form/widget-save') ?>" method="post" class="ajax-validate">
  <fieldset style="margin: 0">
    <legend>
      <span class="prependClose">Добавление виджета</span>
    </legend>

    <div>
      <label>Название (backend)</label>
      <?php echo input_tag('comment') ?>
    </div>

    <div>
      <label>Тип:</label>
      <?php echo select_tag('type', options_for_select(array(
          'integer' => 'целое число',
          'float' => 'дробное число',
          'select' => 'список',
          'multiple' => 'список чекбоксов',
          'radio' => 'выбор радиокнопками',
          'radiocombo' => 'радиокнопки с селектом',
          'price' => 'стоимость (с валютой)',
          'year' => 'год',
          'input' => 'текстовое поле'
          )),
          array('onchange' => "var has_value = {select: true, multiple: true, radio: true, radiocombo: true}; $(this).closest('div').next()[typeof has_value[$(this).val()] == 'undefined' ? 'hide' : 'show']();")
        ) ?>
    </div>

    <div style="display: none;">
      <label>
        Значения<br />
        <small>Множественный выбор &mdash; <br />по одному значению в строку</small>
      </label>
      <?php echo textarea_tag('value', '', 'style=width: 178px; height: 7em;') ?>
    </div>
    <div>
      <label>Название</label>
      <?php echo input_tag('label') ?>
    </div>
    <div>
      <label>Подсказка</label>
      <?php echo input_tag('help') ?>
    </div>
    <div>
      <label>Кол-во баллов</label>
      <?php echo input_tag('rating') ?>
    </div>

    <div style="height: 45px;">
      <label>&nbsp;</label>
      <input class="popupSubmit send" type="submit" value="Добавить" />
    </div>
  </fieldset>
</form>