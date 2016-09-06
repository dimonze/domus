<?php use_helper('I18N', 'Date', 'DomusForm') ?>
<?php include_partial('global/assets') ?>

<div id="sf_admin_container">
  <h1>Описания округов</h1>

  <?php include_partial('global/flashes') ?>

  <div id="sf_admin_content">
    <?= form_tag('settings/districts', 'method=post')?>

      <?php foreach ($data as $region => $districts): ?>
        <fieldset id="sf_fieldset_none">
          <legend><?= $region ?></legend>

          <?php foreach ($districts as $district => $types): ?>
            <fieldset id="sf_fieldset_none">
              <legend><?= $district ?></legend>

              <?php foreach ($types as $name => $value): ?>
                <div class="sf_admin_form_row">
                  <div>
                    <?= label_for("data[$region][$district][$name]", $names[$name]) ?>
                    <?= textarea_tag("data[$region][$district][$name]", $value) ?>
                  </div>
                </div>
              <?php endforeach ?>

            </fieldset>
          <?php endforeach ?>

        </fieldset>
      <?php endforeach ?>

      <ul class="sf_admin_actions form">
        <?= input_tag('submit', 'Сохранить', 'type=submit') ?>
      </ul>

    </form>
  </div>
</div>
