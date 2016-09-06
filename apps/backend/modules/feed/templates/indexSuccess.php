<?php use_helper('I18N', 'Date', 'DomusForm') ?>
<?php include_partial('global/assets') ?>

<div id="sf_admin_container">
  <h1><?php echo __('Настройки экспорта RSS', array(), 'messages') ?></h1>

  <?php include_partial('global/flashes') ?>

  <div id="sf_admin_content">
    <?= form_tag('feed/index', 'method=post')?>
      <?php foreach ($settings as $id => $field): ?>
        <fieldset id="sf_fieldset_none">
          <div class="sf_admin_form_row">
            <div>
              <?= label_for("settings[$id]", $id) ?>
              <div id="rss-config">
                <?php foreach ($field as $field_id => $config): ?>
                  <?= label_for("settings[$id][$field_id]", $field_id) ?>
                  <?= input_tag("settings[$id][$field_id]", $config) ?>
                <?php endforeach ?>
              </div>
            </div>
          </div>
        </fieldset>
      <?php endforeach ?>

      <ul class="sf_admin_actions form">
        <?= input_tag('submit', 'Сохранить', 'type=submit') ?>
      </ul>

    </form>
  </div>
</div>
