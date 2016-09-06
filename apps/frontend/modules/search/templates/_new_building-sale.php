<?php include_partial('form/price-box', array('name' => 'Цена за м<sup>2</sup>')) ?>

<div class="squareBox">
  <h3>Площадь, м<sup>2</sup></h3>
  <?php include_partial('form/configured-select', array('type' => 'new_building-sale', 'id' => 'field72_from', 'attr' => 'class=select_02')) ?>
  -
  <?php include_partial('form/configured-select', array('type' => 'new_building-sale', 'id' => 'field73_to', 'attr' => 'class=select_02')) ?>
  <a href="#" class="link2 set-custom">Изменить значения</a>
</div>

<div class="roomsBox">
  <h3>Количество комнат</h3>
  <label><input name="field[76][or][]" type="checkbox" value="1" /> 1</label>
  <label><input name="field[76][or][]" type="checkbox" value="2" /> 2</label>
  <label><input name="field[76][or][]" type="checkbox" value="3" /> 3</label>
  <label><input name="field[76][or][]" type="checkbox" value="4" /> 4</label>
  <label><input name="field[76][or][]" type="checkbox" value="5" /> 5+</label>
  <br />
  <label><input name="field[76][or][]" type="checkbox" value="своб. планировка" /> свободная планировка</label>
</div>
<h3>Адрес или название ЖК</h3>
<div class="sortBox text-search" style="border:none;margin:0 0 10px 0;padding: 6px 10px 6px 0;">
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

<div class="advancedSearch"> 
  <a href="#" class="advancedClose"><span>Расширенный поиск</span></a> 

  <div class="boxBack_06" style="display: none;"> 
    <h3>Стадии строительства</h3> 
<!--    <div class="inputBox"> 
      <label><input type="checkbox" name="field[74][]" value="сдан"/> Дом сдан</label> 
      <label><input type="checkbox" name="field[74][]" value="построен"/> Дом построен</label> 
      <label><input type="checkbox" name="field[74][]" value="строится"/> На стадии строительства</label> 
      <label><input type="checkbox" name="field[74][]" value="котлован"/> На стадии котлована</label> 
      <label><input type="checkbox" name="field[74][]" value="проект"/> На стадии проектирования</label> 
    </div> -->
    <?php include_component('form', 'renderField', array('id' => 74, 'class' => 'select_04', 'name' => 'field[%s]')) ?>
    <h3>Удаленность от  ж/д, метро</h3> 
    <div class="controlBox"> 
      от <input name="field[75][from]" type="text" class="input_07"/> 
      до
      <input name="field[75][to]" type="text" class="input_07"/>, м
    </div> 
    <div class="searchObjectButton"> 
      <span class="formButton"><input type="submit" value="Найти"/></span> 
    </div> 
  </div> 
</div> 