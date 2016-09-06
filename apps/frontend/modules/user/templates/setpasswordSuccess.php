<div class="contentLeft_02">
  <div class="textBox setPassword">
    
    <form action="<?= url_for('user/setpassword') ?>" method="post">
      <?= input_hidden_tag('key', $sf_params->get('key')) ?>

      <div>
        <label for="password">Новый пароль</label>
        <?= $form['password'] ?>
        <?= $form['password']->renderError() ?>
      </div>

      <div>
        <label for="password_again">Повторите пароль</label>
        <?= $form['password_again'] ?>
        <?= $form['password_again']->renderError() ?>
      </div>

      <div>
        <?= submit_tag('Сменить') ?>
      </div>

    </form>

  </div>
</div>