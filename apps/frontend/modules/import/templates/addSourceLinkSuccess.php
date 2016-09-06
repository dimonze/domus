<?php if ($form): ?>
  <form action="<?= url_for('@form?action=addSourceLink') ?>" method="post" class="ajax-validate">
    <fieldset>
      <legend>
        <span class="prependClose">
          Добавление ссылки на файл
        </span>
      </legend>
      <div><div>
        <?= $form['url']->renderLabel() ?>
        <?= $form['url']->render() ?>
      </div></div>
      <div><div>
        <?= $form['type']->renderLabel() ?>
        <?= $form['type']->render() ?>
      </div></div>
      <div><div>
        <?= $form['file_type']->renderLabel() ?>
        <?= $form['file_type']->render() ?>
      </div></div>
      <div style="height: 30px;">
        <input class="popupSubmit send" type="submit" value="Добавить" />
      </div>
    </fieldset>
  </form>
<?php endif ?>