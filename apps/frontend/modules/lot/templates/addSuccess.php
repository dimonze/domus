<?php foreach($form->getJavaScripts() as $js) use_javascript($js) ?>
<?php foreach($form->getStylesheets() as $css) use_stylesheet($css) ?>

<div class="contentLeft_02">
  <?php include_partial('import/package-import-adv') ?>
  <form action="<?= url_for('lot/add') ?>" method="post" class="addForm ajax-validate">

    <div class="addForm-head sortBox_02">
      <div class="floatLeft">
        <label><strong>Я предлагаю</strong></label>
          <?= $form['type'] ?>
          <?= $form['type']->renderError() ?>

      </div>
      <div class="floatRight">
        <label>
          <strong>Период публикации </strong>
          <?= $form['period'] ?>
          <?= $form['period']->renderError() ?>
        </label>
      </div>
      <div class="clearBoth"></div>
    </div>
  
    <?php if ($form['type']->getValue()): ?>
      <table width="100%" cellpadding="0" cellspacing=".">
        <tbody>
          <?php $type = $form['type']->getValue() ?>
          <?php unset($form['type'], $form['period'], $form['id']) ?>
          <?php include_partial('lot/form_body', array('form' => $form, 'type' => $type)) ?>
        </tbody>
        <tfoot>
          <tr class="addButton">
            <td colspan="2">
              <span class="formButton"><input type="submit" value="Добавить обьявление" /></span>
              <div class="buttonBox">&nbsp;</div>
              <span class="formButton"><input type="reset" value="Очистить" /></span>
            </td>
          </tr>
        </tfoot>
      </table>
    <?php endif ?>
  </form>
</div>
