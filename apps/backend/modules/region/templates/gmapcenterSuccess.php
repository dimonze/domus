<?php use_helper('DomusForm') ?>

<form action="<?= url_for('region/gmapcenter') ?>" method="post" class="ajax-validate">
  <?= input_hidden_tag('id', $region->id) ?>

  <fieldset style="margin: 0">
    <legend>
      <span class="prependClose">
        Центр на карте "<?= $region->name ?>"
      </span>
    </legend>

    <div>
      <div>
        <?= label_for('latitude', 'Широта') ?>
        <?= input_tag('latitude', $region->latitude) ?>
      </div>
    </div>

    <div>
      <div>
        <?= label_for('longitude', 'Долгота') ?>
        <?= input_tag('longitude', $region->longitude) ?>
      </div>
    </div>

    <div>
      <div>
        <?= label_for('zoom', 'Зум') ?>
        <?= input_tag('zoom', $region->zoom) ?>
      </div>
    </div>

    <div>
      <input type="submit" value="Сохранить" class="popupSubmit send" />
    </div>
  </fieldset>
</form>