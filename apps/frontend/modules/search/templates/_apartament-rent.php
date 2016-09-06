<?php include_partial('form/price-box', array('name' => 'Цена')) ?>

<div class="squareBox">
  <h3>Площадь, м<sup>2</sup></h3>
  <?php
    include_partial(
      'form/configured-select',
      array(
        'type' => 'apartament-rent', 'id' => 'field1_from',
        'attr' => 'class=select_02'
      )
    )
  ?>
  -
  <?php
    include_partial(
      'form/configured-select',
      array(
        'type' => 'apartament-rent', 'id' => 'field1_to',
        'attr' => 'class=select_02'
      )
    )
  ?>
  <a href="#" class="link2 set-custom">Изменить значения</a>
</div>

<div class="roomsBox">
  <h3>Количество комнат</h3>
  <label><input name="field[55][or][]" type="checkbox" value="комната" /> комната</label>
  <br />
  <label><input name="field[55][or][]" type="checkbox" value="1 комнатная квартира" /> 1</label>
  <label><input name="field[55][or][]" type="checkbox" value="2-х комнатная квартира" /> 2</label>
  <label><input name="field[55][or][]" type="checkbox" value="3-х комнатная квартира" /> 3</label>
  <label><input name="field[55][or][]" type="checkbox" value="4-х комнатная квартира" /> 4</label>
  <label><input name="field[55][or][]" type="checkbox" value="5+-?и комнатная квартира" /> 5+</label>
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
    <h3>Состояние/ремонт</h3>
    <?php include_component('form', 'renderField', array('id' => 17, 'class' => 'select_04', 'name' => 'field[%s]')) ?>

    <div class="inputBox">
      <label>
        <input name="field[18]" type="checkbox" value="да" />
        с мебелью
      </label>
      <label>
        <input name="field[19]" type="checkbox" value="да" />
        с оборудованием / бытовой техникой
      </label>
      <label>
        <input name="field[21][]" type="checkbox" value="гараж/машиноместо" />
        с гаражем / машиноместом
      </label>
    </div>

    <div class="searchObjectButton">
      <span class="formButton"><input type="submit" value="Найти" /></span>
    </div>

  </div>

</div>