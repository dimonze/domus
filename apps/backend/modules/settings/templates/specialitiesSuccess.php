<?php use_helper('I18N', 'Date', 'DomusForm') ?>
<?php include_partial('global/assets') ?>

<div id="sf_admin_container">
  <h1>Специальности:</h1>

  <?php include_partial('global/flashes') ?>

  <div id="sf_admin_content">
    <?= form_tag('settings/specialities', 'method=post')?>

      <p>Вводите значения по одному в строку.</p>

      <fieldset id="sf_fieldset_none"">
        <div class="sf_admin_form_row">
          <div>
            <?= textarea_tag('types', implode("\n", $types), 'style=width: 40em; height: 15em;') ?>
          </div>
        </div>
      </fieldset>

      <ul class="sf_admin_actions form">
        <?= input_tag('submit', 'Сохранить', 'type=submit') ?>
      </ul>

    </form>
  </div>
</div>