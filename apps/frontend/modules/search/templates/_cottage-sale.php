<div class="roomsBox">
  <h3>Тип предложения</h3>
  <select name="field[107][or][]" id="field107" class="select_04">
    <option value="">Все</option>
    <option value="Дом/Коттедж">Дом/Коттедж</option>
    <option value="Таунхаусы и Дуплексы">Таунхаусы и Дуплексы</option>
    <option value="Участок">Участок</option>
    <option value="Участок с подрядом">Участок с подрядом</option>
  </select>
</div>

<div class="squareBox">
  <h3>Расстояние до МКАД, км</h3>
  <?php include_partial('form/configured-select', array('type' => 'cottage-sale', 'id' => 'field92_from', 'attr' => 'class=select_02')) ?>
  -
  <?php include_partial('form/configured-select', array('type' => 'cottage-sale', 'id' => 'field92_to', 'attr' => 'class=select_02')) ?>
  <a href="#" class="link2 set-custom">Изменить значения</a>
</div>

<?php include_partial('form/price-box', array('name' => 'Цена')) ?>

<div class="squareBox">
  <h3>Площадь дома, м<sup>2</sup></h3>
  <?php include_partial('form/configured-select', array('type' => 'cottage-sale', 'id' => 'square_from', 'attr' => 'class=select_02')) ?>
  -
  <?php include_partial('form/configured-select', array('type' => 'cottage-sale', 'id' => 'square_to', 'attr' => 'class=select_02')) ?>
  <a href="#" class="link2 set-custom">Изменить значения</a>
</div>

<div class="squareBox">
  <h3>Площадь участка, сот.</h3>
  <?php include_partial('form/configured-select', array('type' => 'cottage-sale', 'id' => 'field94_from', 'attr' => 'class=select_02')) ?>
  -
  <?php include_partial('form/configured-select', array('type' => 'cottage-sale', 'id' => 'field95_to', 'attr' => 'class=select_02')) ?>
  <a href="#" class="link2 set-custom">Изменить значения</a>
</div>

<?php /*<div class="squareBox">
  <h3>Включая вторичный рынок</h3>
  <select name="field[108]" id="secondary-market" class="select_04">
    <option value="нет">Нет</option>
    <option value="да">Да</option>
  </select>
</div>*/ ?>

<h3>Адрес или название</h3>
<div class="sortBox text-search" style="border:none;margin:0;padding: 6px 10px 6px 0;">
  <a href="#" class="update-search-text">искать</a>
  <?= input_tag('q_text', $sf_params->get('q'), array(
      'class' => 'autocomplete-street'
      ,'id' => 'frontstreet'
      ,'source' => url_for('form/street')
      ,'style' => 'width:130px;margin-right:0;'
  )) ?>

  <?php if ($sf_params->has('type')): ?>
    <br /><?= input_tag('q_text_enabled', '1', 'type=checkbox checked=checked style="margin-left:0px;"') ?>
    <?= label_for('q_text_enabled', 'учитывать текстовый поиск') ?>
  <?php endif ?>
</div>