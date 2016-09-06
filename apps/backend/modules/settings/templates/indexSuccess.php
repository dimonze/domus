<?php use_helper('I18N', 'Date', 'DomusForm') ?>
<?php include_partial('global/assets') ?>

<div id="sf_admin_container">
  <h1><?php echo __('Настройки', array(), 'messages') ?></h1>

  <?php include_partial('global/flashes') ?>

  <div id="sf_admin_content">
    <?= form_tag('settings/index', 'method=post')?>
      <?php foreach ($settings as $id => $field): ?>
        <fieldset id="sf_fieldset_none">
          <div class="sf_admin_form_row">
            <div>
              <?= label_for("settings[$id]", $field['name']) ?>
              <?php if (is_bool($field['value'])): ?>
                <?= checkbox_tag("settings[$id]", $field['value'], $field['value']) ?>

              <?php elseif (isset($field['type']) && $field['type'] == 'textarea'): ?>
                <?= textarea_tag("settings[$id]", $field['value']) ?>

              <?php else: ?>
                <?= input_tag("settings[$id]", $field['value']) ?>
              
              <?php endif ?>
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
