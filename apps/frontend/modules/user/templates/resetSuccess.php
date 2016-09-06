<form action="<?= url_for('user/reset') ?>" method="post" class="ajax-validate">
  <fieldset>
    <legend>
      <span class="prependClose">Восстановление пароля</span>
      </legend>

    <div>
      <label for="email">E-mail</label>
      <?= $form['email']->render() ?>
    </div>

    <ul>
       <li><?= link_to('Авторизоваться', 'user/login?forward=' . $sf_params->get('forward'),
                       'class=popup rel=loginwindow') ?></li>
       <li><?= link_to('Регистрация', 'user/register?forward=' . $sf_params->get('forward'),
                       'class=popup rel=reg') ?></li>
    </ul>

    <div>
       <input class="popupSubmit login" type="submit" value="Выслать пароль" />
    </div>
  </fieldset>
</form>