<?php use_helper('DomusForm') ?>

<form action="<?= url_for('region/rayontext') ?>" method="post" class="ajax-validate">
  <?= input_hidden_tag('id', $region->id) ?>

  <fieldset style="margin: 0">
    <legend>
      <span class="prependClose">
        SEO-текст для районов в "<?= $region->name ?>"
      </span>
    </legend>

    <div>
      <div>
        <?= label_for('rayontext', 'Текст') ?>
      </div>
      <div>
        <?= textarea_tag('rayontext', $region->rayontext, array('size' => '40x25', 'style' => 'width: 30em')) ?>
      </div>
    </div>

    <div>
      <input type="submit" value="Сохранить" class="popupSubmit send" />
    </div>
  </fieldset>
</form>