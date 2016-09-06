<?php include_partial('form/price-box', array('name' => 'Цена')) ?>

<div class="squareBox">
  <h3>Площадь, м<sup>2</sup></h3>
  <?php include_partial('form/configured-select', array('type' => 'apartament-sale', 'id' => 'field1_from', 'attr' => 'class=select_02')) ?>
  -
  <?php include_partial('form/configured-select', array('type' => 'apartament-sale', 'id' => 'field1_to', 'attr' => 'class=select_02')) ?>
  <a href="#" class="link2 set-custom">Изменить значения</a>
</div>

<div class="roomsBox">
  <h3>Количество комнат</h3>
  <label><input name="field[54][or][]" type="checkbox" value="комната" /> комната</label>
  <br />
  <label><input name="field[54][or][]" type="checkbox" value="1 комнатная квартира" /> 1</label>
  <label><input name="field[54][or][]" type="checkbox" value="2-х комнатная квартира" /> 2</label>
  <label><input name="field[54][or][]" type="checkbox" value="3-х комнатная квартира" /> 3</label>
  <label><input name="field[54][or][]" type="checkbox" value="4-х комнатная квартира" /> 4</label>
  <label><input name="field[54][or][]" type="checkbox" value="5+-?и комнатная квартира" /> 5+</label>
  <br />
  <label><input name="field[54][or][]" type="checkbox" value="квартира со свободной планировкой" /> свободная планировка</label>
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
    <h3>Тип дома</h3>
    <?php include_component('form', 'renderField', array('id' => 6, 'class' => 'select_04', 'name' => 'field[%s]', 'empty' => 'Любой')) ?>

    <h3>Год постройки/сдачи</h3>
    <input name="field[5][from]" type="text" class="input_03" />
    -
    <input name="field[5][to]" type="text" class="input_03" />
    <br />

    <div class="inputBox">
      <label>
        <input name="field[20][]" type="checkbox" value="Подземная автостоянка" />
        подземная автостоянка
      </label>
      <label>
        <input name="field[20][]" type="checkbox" value="С отделкой" />
        с отделкой
      </label>
    </div>

    <div class="searchObjectButton">
      <span class="formButton"><input type="submit" value="Найти" /></span>
    </div>

  </div>

</div>