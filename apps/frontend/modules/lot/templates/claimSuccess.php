<form action="<?= url_for('/lot/'.$sf_params->get('id').'/claim') ?>" method="post" class="ajax-validate new-popup">
  <?= $form['lot_id'] ?>
  <fieldset>
    <legend><span class="prependClose">Пожаловаться на объявление</span></legend>
    <?php if(!$sf_user->isAuthenticated()): ?>
    <div>
      <label for="claim_user_name">Ваше имя</label>
      <?= $form['user_name']->render() ?>
    </div>
    <div>
      <label for="claim_user_email">Ваш e-mail</label>
      <?= $form['user_email']->render() ?>
    </div>
    <?php endif ?>
    <div>
      <label for="claim_theme_id">Тема жалобы</label>
      <?= $form['claim_theme_id']->render() ?>
    </div>
    <div>
      <label for="claim_body">Сообщение</label>
      <?= $form['body']->render() ?>
    </div>

    <?php if(!$sf_user->isAuthenticated()): ?>
    <div>
      <label for="claim_captcha">Символы с картинки</label>
      <?= $form['captcha']->render() ?>
    </div>
    <?php endif ?>

    <div  class="buttonDiv">
	   <span class="formButton"><input type="submit" value="Пожаловаться"/></span>
    </div>
  </fieldset>
</form>