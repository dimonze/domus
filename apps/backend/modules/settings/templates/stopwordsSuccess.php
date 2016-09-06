<?php use_helper('I18N', 'Date', 'DomusForm') ?>
<?php include_partial('global/assets') ?>

<div id="sf_admin_container">
  <h1>Стоп-слова:</h1>

  <?php include_partial('global/flashes') ?>

  <div id="sf_admin_content">
    <?= form_tag('settings/stop-words', 'method=post')?>

      <p>Вводите значения по одному в строку.</p>

      <fieldset id="sf_fieldset_none"">
        <div class="sf_admin_form_row">
          <div>
            <?= textarea_tag('words', implode("\n", $words), 'style=width: 40em; height: 15em;') ?>
          </div>
        </div>
      </fieldset>
      <fieldset id="sf_fieldset_none">
        <div class="sf_admin_form_row">          
          <?= label_for('default_email_theme', 'Тема сообщения для автоматической блокировки')?>
          <?= select_tag('default_email_theme', options_for_select($email_themes['titles'], $default_email_theme)) ?>
          <?php foreach ($email_themes['body'] as $title => $body): ?>
            <?= input_hidden_tag('email_theme[' . $title . ']', $body) ?>
          <?php endforeach ?>
          <div>
            <span id="message_body"><?= isset($email_themes['body'][$default_email_theme]) ? $email_themes['body'][$default_email_theme] : ''; ?></span>
          </div>
        </div>        
      </fieldset>
      <ul class="sf_admin_actions form">
        <?= input_tag('submit', 'Сохранить', 'type=submit') ?>
      </ul>

    </form>
  </div>
</div>