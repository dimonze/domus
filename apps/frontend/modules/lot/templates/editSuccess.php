<?php foreach($form->getJavaScripts() as $js) use_javascript($js) ?>
<?php foreach($form->getStylesheets() as $css) use_stylesheet($css) ?>

<div class="contentLeft_02">
  <form action="<?= url_for('lot/edit') ?>" method="post" class="addForm ajax-validate">
  <?php $widgetSchema = $form->getWidgetSchema() ?>
  <?= input_hidden_tag($widgetSchema->generateName('id'), $form['id']->getValue()) ?>
  <?= input_hidden_tag($widgetSchema->generateName('type'), $form['type']->getValue()) ?>
  <?= input_hidden_tag($widgetSchema->generateName('period'), $form['period']->getValue()) ?>

  <table width="100%" cellpadding="0" cellspacing=".">
    <tbody>
      <?php $type = $form['type']->getValue(); ?>
      <?php unset($form['type'], $form['period'], $form['id']) ?>
      <?php include_partial('lot/form_body', array('form' => $form, 'type' => $type)) ?>
    </tbody>
    <tfoot>
      <tr class="addButton">
        <td colspan="2">
          <span class="formButton"><input type="submit" value="Обновить обьявление" /></span>
          <div class="buttonBox">&nbsp;</div>
          <span class="formButton"><input type="reset" value="Очистить" /></span>
        </td>
      </tr>
    </tfoot>
  </table>
  </form>
</div>
