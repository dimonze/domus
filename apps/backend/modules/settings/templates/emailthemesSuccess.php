<?php use_helper('I18N', 'Date', 'DomusForm') ?>
<?php include_partial('global/assets') ?>

<div id="sf_admin_container">
  <h1>Темы сообщений:</h1>

  <?php include_partial('global/flashes') ?>

  <div id="sf_admin_content">
    <?= form_tag('settings/emailthemes', 'method=post')?>
      <fieldset id="sf_fieldset_none">
        <?php foreach ($data as $key => $value): ?>
          <fieldset id="sf_fieldset_none">
            <legend><?= $key ?></legend>
            <div class="sf_admin_form_row">
              <div>
                <?= label_for("data[$key][body]", 'Текст сообщения') ?>
                <?= textarea_tag("data[$key][body]", $value['body'], array('style' => 'width: 300px; height: 100px;')) ?>
              </div>
              <?= link_to('Удалить', '@emailtheme_delete?title=' . $key) ?>
            </div>
          </fieldset>
        <?php endforeach ?>
      </fieldset>
      <ul class="sf_admin_actions form">
        <?= input_tag('submit', 'Сохранить', 'type=submit') ?>
        <?= link_to('Добавить тему', '@emailthemes_new') ?>
      </ul>

    </form>
  </div>
</div>