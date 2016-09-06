<?php use_helper('DomusForm') ?>

<form action="<?= url_for('region/shossetext') ?>" method="post" class="ajax-validate">
  <?= input_hidden_tag('id', $region->id) ?>

  <fieldset style="margin: 0">
    <legend>
      <span class="prependClose">
        SEO-текст для шоссе в "<?= $region->name ?>"
      </span>
    </legend>

    <div>
      <div>
        <?= label_for('rayontext', 'Текст') ?>
      </div>
      <div>
        <?= textarea_tag('shossetext', $region->shossetext, array('size' => '40x25', 'style' => 'width: 30em')) ?>
      </div>
    </div>

    <div>
      <input type="submit" value="Сохранить" class="popupSubmit send" />
    </div>
  </fieldset>
</form>