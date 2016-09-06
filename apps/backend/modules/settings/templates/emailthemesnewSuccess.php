<?php use_helper('I18N', 'Date', 'DomusForm') ?>
<?php include_partial('global/assets') ?>

<div id="sf_admin_container">
  <h1>Добавить новую тему:</h1>

  <?php include_partial('global/flashes') ?>

  <div id="sf_admin_content">
    <?= form_tag('@emailthemes_new', 'method=post')?>
      <div class="sf_admin_form_row">
        <div>
          <?= label_for('data[title]', 'Тема: ') ?>
          <?= input_tag('data[title]', '', array('style' => 'width: 300px;')) ?><br>
          <?= label_for('data[body]', 'Сообщение: ') ?>
          <?= textarea_tag('data[body]', '', array('style' => 'width: 300px; height: 100px;')) ?>
        </div>
      </div>
      <ul class="sf_admin_actions form">
        <?= input_tag('submit', 'Сохранить', 'type=submit') ?>
      </ul>

    </form>
  </div>
</div>