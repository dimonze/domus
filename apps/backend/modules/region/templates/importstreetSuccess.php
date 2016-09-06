<?php use_helper('DomusForm') ?>

<form action="<?= url_for('region/importstreet') ?>" method="post" class="ajax-validate">
  <?= input_hidden_tag('id', $regionnode->id) ?>
  <fieldset style="margin: 0">
    <legend>
      <span class="prependClose">
        Улицы <?= $regionnode->full_name ?>
      </span>
    </legend>

    <div>
      Формат: «название|сокращение», например, «Ленина|ул»<br />
      По 1 улице в строку.
    </div>

    <div>
      <textarea name="data" style="width: 88%; height: 12em;"></textarea>
    </div>

    <div>
      <input type="submit" value="Добавить" class="popupSubmit send" />
    </div>
  </fieldset>
</form>