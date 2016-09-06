<?php include_partial('form/price-box', array('name' => 'Цена')) ?>

<div class="squareBox">
  <h3>Площадь помещения, м<sup>2</sup></h3>
  <?php include_partial('form/configured-select', array('type' => 'commercial-sale', 'id' => 'field46_from', 'attr' => 'class=select_02')) ?>
  -
  <?php include_partial('form/configured-select', array('type' => 'commercial-sale', 'id' => 'field46_to', 'attr' => 'class=select_02')) ?>
  <a href="#" class="link2 set-custom">Изменить значения</a>
</div>

<div class="squareBox">
  <h3>Площадь участка, га</h3>
  <?php include_partial('form/configured-select', array('type' => 'commercial-sale', 'id' => 'field47_from', 'attr' => 'class=select_02')) ?>
  -
  <?php include_partial('form/configured-select', array('type' => 'commercial-sale', 'id' => 'field47_to', 'attr' => 'class=select_02')) ?>
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

    <h3>Год постройки/сдачи</h3>
    <input name="field[5][from]" type="text" class="input_03" />
    -
    <input name="field[5][to]" type="text" class="input_03" />

    <br /><br />

    <div class="searchObjectButton">
      <span class="formButton"><input type="submit" value="Найти" /></span>
    </div>

  </div>

</div>