<form action="<?= url_for('user/login') ?>" method="post" class="ajax-validate">
  <input type="hidden" name="forward" value="<?= $sf_params->get('forward') ?>" />
  <fieldset>
    <legend>
      <span class="prependClose">Вход</span>
    </legend>

    <div>
      <label for="email">E-mail</label>
      <?= $form['email']->render() ?>
    </div>
    <div>
      <label for="pass">Пароль</label>
      <?= $form['password']->render() ?>
    </div>

    <ul>
       <li><?= link_to('Забыли пароль?', 'user/reset?forward=' . $sf_params->get('forward'),
                       'class=popup rel=loginwindow
                       ') ?></li>
       <li><?= link_to('Регистрация', 'user/register?forward=' . $sf_params->get('forward'),
                       'class=popup rel=reg') ?></li>
    </ul>
    <div>
       <input class="popupSubmit login" type="submit" value="Войти" />
       <label class="popupLabel">
        <?= $form['remember']->render() ?>
        <span>Запомнить меня</span>
      </label>
    </div>
  </fieldset>
</form>