<?php use_helper('I18N', 'Date', 'DomusForm') ?>
<?php include_partial('global/assets') ?>

<div id="sf_admin_container">
  <h1><?php echo __('Настройки бокового блока', array(), 'messages') ?></h1>

  <?php include_partial('global/flashes') ?>

  <div id="sf_admin_content">
    <?= form_tag('settings/aside', 'method=post')?>
      <?php foreach ($data as $modul_name => $modul): ?>
        <fieldset id="sf_fieldset_none">
          <legend><?= $modul_label[$modul_name] ?></legend>
        <div class="sf_admin_form_row">
          <div>
          <?php foreach($modul as $action_name => $action): ?>
            <div class="aside-float-left">
              <h3><?= $action_label[$action_name] ?></h3>
              <ul>
            <?php foreach($action as $aside_name => $aside): ?>
                <li>
              <?= label_for("data[$modul_name][$action_name][$aside_name]", $aside_label[$aside_name]) ?>
              <?= checkbox_tag("data[$modul_name][$action_name][$aside_name]", $aside, $aside) ?>
                </li>
            <?php endforeach ?>
                </ul>
            </div>
          <?php endforeach ?>
          </div>
         </div>
        </fieldset>
        <ul class="sf_admin_actions form">
          <?= input_tag('submit', 'Сохранить', 'type=submit') ?>
        </ul>
      <?php endforeach ?>



    </form>
  </div>
</div>
