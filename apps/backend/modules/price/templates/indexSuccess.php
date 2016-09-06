<?php use_helper('I18N', 'Date', 'DomusForm') ?>
<?php include_partial('global/assets') ?>

<div id="sf_admin_container">
  <h1><?php echo __('Стоимость импорта', array(), 'messages') ?></h1>

  <?php include_partial('global/flashes') ?>

  <div id="sf_admin_content">
    <?= form_tag('price/index', 'method=post')?>
      <fieldset id="sf_fieldset_none">
        <legend>Разделы недвижимости</legend>
        <?php foreach($prices as $type => $price): ?>
        <div class="sf_admin_form_row">
          <?= label_for("prices[$type]", $type) ?>
          <?= input_tag("prices[$type]", $price) ?>
        </div>
        <?php endforeach; ?>
      </fieldset>
      <ul class="sf_admin_actions form">
        <?= input_tag('submit', 'Сохранить', 'type=submit') ?>
      </ul>
    </form>
  </div>
</div>
