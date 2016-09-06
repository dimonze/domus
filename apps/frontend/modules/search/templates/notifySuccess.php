<form action="<?= url_for('search/notify') ?>" method="post" class="ajax-validate">
  <fieldset>
    <legend>
      <span class="prependClose">Подписка на обновления</span>
    </legend>

    <div>
      <label for="email">E-mail</label>
      <?= $form['email']->render() ?>
    </div>
    <div>
      <label for="period">Частота рассылки</label>
      <?= $form['period']->render() ?>
    </div>

    <div class="buttonDiv">
       <!-- <input class="popupSubmit login" type="submit" value="Подписаться" />-->
	   <span class="formButton"><input type="submit" value="Подписаться"/></span>
    </div>
  </fieldset>
</form>