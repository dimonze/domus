<?php include_partial('form/price-box', array('name' => 'Цена в мес.')) ?>

<div class="squareBox">
  <h3>Площадь дома, м<sup>2</sup></h3>
  <?php include_partial('form/configured-select', array('type' => 'house-rent', 'id' => 'field26_from', 'attr' => 'class=select_02')) ?>
  -
  <?php include_partial('form/configured-select', array('type' => 'house-rent', 'id' => 'field26_to', 'attr' => 'class=select_02')) ?>
  <a href="#" class="link2 set-custom">Изменить значения</a>
</div>

<div class="squareBox">
  <h3>Площадь участка, соток</h3>
  <?php include_partial('form/configured-select', array('type' => 'house-rent', 'id' => 'field27_from', 'attr' => 'class=select_02')) ?>
  -
  <?php include_partial('form/configured-select', array('type' => 'house-rent', 'id' => 'field27_to', 'attr' => 'class=select_02')) ?>
  <a href="#" class="link2 set-custom">Изменить значения</a>
</div>

<div class="sortBox text-search">
  <a href="#" class="update-search-text">искать</a>
  <?= input_tag('q_text', $sf_params->get('q'), array(
      'class' => 'autocomplete-street'
      ,'id' => 'frontstreet'
      ,'source' => url_for('form/street')
  )) ?>

  <?php if ($sf_params->has('type')): ?>
    <br /><?= input_tag('q_text_enabled', '1', 'type=checkbox checked=checked') ?>
    <?= label_for('q_text_enabled', 'учитывать текстовый поиск') ?>
  <?php endif ?>
</div>

<div class="advancedSearch">
  <a href="#" class="advancedClose"><span>Расширенный поиск</span></a>

  <div class="boxBack_06" style="display: none;">
    <?php include_component('form', 'preloadFields', array('id' => array(18,19,28,61,60,56,57,58,59))) ?>

    <h3>Год постройки/сдачи</h3>
    <fieldset>
    <input name="field[5][from]" type="text" class="input_03" />
    -
    <input name="field[5][to]" type="text" class="input_03" />
    </fieldset>
    <h3>Тип дома</h3>
    <?php include_component('form', 'renderField', array('id' => 28, 'class' => 'select_04', 'name' => 'field[%s]', 'empty' => 'Любой')) ?>

    <h3>Ремонт/состояние</h3>
    <?php include_component('form', 'renderField', array('id' => 61, 'class' => 'select_04', 'name' => 'field[%s]')) ?>

    <h3>Электричество</h3>
    <?php include_component('form', 'renderField', array('id' => 57, 'class' => 'select_04', 'name' => 'field[%s]')) ?>

    <h3>Газ</h3>
    <?php include_component('form', 'renderField', array('id' => 56, 'class' => 'select_04', 'name' => 'field[%s]')) ?>

    <h3>Водопровод</h3>
    <?php include_component('form', 'renderField', array('id' => 58, 'class' => 'select_04', 'name' => 'field[%s]')) ?>

    <h3>Отопление</h3>
    <?php include_component('form', 'renderField', array('id' => 60, 'class' => 'select_04', 'name' => 'field[%s]')) ?>

    <h3>Канализация</h3>
    <?php include_component('form', 'renderField', array('id' => 59, 'class' => 'select_04', 'name' => 'field[%s]')) ?>

    <div class="inputBox">
      <label>
        <input name="field[18]" type="checkbox" value="да" />
        с мебелью
      </label>
      <label>
        <input name="field[19]" type="checkbox" value="да" />
        с оборудованием / бытовой техникой
      </label>
    </div>

    <div class="searchObjectButton">
      <span class="formButton"><input type="submit" value="Найти" /></span>
    </div>

  </div>

</div>