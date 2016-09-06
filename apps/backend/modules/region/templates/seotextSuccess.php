<?php use_helper('DomusForm') ?>

<form action="<?= url_for('region/seotext') ?>" method="post" class="ajax-validate">
  <?= input_hidden_tag('id', $region->id) ?>

  <fieldset style="margin: 0">
    <legend>
      <span class="prependClose">
        SEO-текст "<?= $region->name ?>"
      </span>
    </legend>

    <div>
      <div>
        <?= label_for('seotext', 'Текст') ?>
      </div>
      <div>
        <?= textarea_tag('seotext', $region->seotext, array('size' => '40x25', 'style' => 'width: 30em')) ?>
      </div>
    </div>

    <div>
      <input type="submit" value="Сохранить" class="popupSubmit send" />
    </div>
  </fieldset>
</form>