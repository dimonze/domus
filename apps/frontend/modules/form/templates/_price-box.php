<div class="priceSelectBox">
  <h3><?= empty($name) ? 'Цена' : $name ?></h3>
  <?php $currency_types = FormField::getCurrencyTypes($sf_params->get('type'))?>
  <?php if (!empty($currency_types)): ?>
    <ul class="currencyType">
    <?php $type = $sf_params->get('type') ?>
    <?php foreach ($currency_types as $value => $name): ?>
      <?php if (($type == 'commercial-rent' && $value == 'год')
            || ($type == 'apartament-rent' && $value == 'месяц')): ?>
        <li selected="selected">
          <a href="#" class="active" rel="<?= array_search($value, Lot::$currency_types[$type]) ?>"><?= $name ?></a>
        </li>
      <?php else: ?>
        <li>
          <a href="#" rel="<?= array_search($value, Lot::$currency_types[$type]) ?>"><?= $name ?></a>
        </li>
      <?php endif ?>
    <?php endforeach ?>
    </ul>
  <?php endif ?>
  <ul class="currencyList <?php if (!empty($currency_types)): ?>tothebottom<?php endif ?>">
    <li><a href="#" class="active" rel="RUR">p.</a></li>
    <li><a href="#" rel="USD">$</a></li>
    <li><a href="#" rel="EUR">&euro;</a></li>
  </ul>

  <div class="clearBoth"></div>

  <select name="price[from]" class="select_02"></select>
  -
  <select name="price[to]" class="select_02"></select>
  <a href="#" class="link2 set-custom">Изменить значения</a>
</div>