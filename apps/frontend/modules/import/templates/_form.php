<?php if ($form): ?>
<form id="import_form" action="<?= url_for('@import?action=importFile') ?>" method="post" enctype="multipart/form-data">
  <div class="row">
    <div class="custom-select">
      <?= $form['format']->renderLabel() ?>
      <?= $form['format']->render() ?>
      <?= $form['format']->renderError() ?>
    </div>
    <div class="custom-select">
      <?= $form['type']->renderLabel() ?>
      <?= $form['type']->render() ?>
      <?= $form['type']->renderError() ?>
    </div>
    <div class="custom-select">
      <?= $form['frequency']->renderLabel() ?>
      <?= $form['frequency']->render() ?>
      <?= $form['frequency']->renderError() ?>
    </div>
  </div>

  <div class="row">
    <h4>Выберите вариант загрузки файла</h4>

    <div class="tabs">
      <ul>
        <li class="current"><a href="#">Указать URL файла</a><i></i></li>
        <li class="last"><a href="#">Загрузить файл с компьютера</a><i></i></li>
      </ul>
      <div>
        <?= $form['url']->render() ?>
      </div>
      <div class="hidden">
        <?= $form['file']->render() ?>
      </div>
    </div>
    <div class="clearBoth"></div>
    <?= $form['file']->renderError() ?>
    <?= $form['url']->renderError() ?>
    <span class="formButton"><input name="input" type="submit" value="Отправить"></span>
  </div>
</form>
<?php endif ?>