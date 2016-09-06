<?php foreach($form as $field_name => $field): ?>
  <?php $widget = $field->getWidget()  ?>

  <?php if($widget->getOption('type') == 'hidden'): ?>
    <?php continue ?>
  <?php endif ?>

  <tr class="form-<?= $field_name ?>">
    <?php if (!$widget->getAttribute('need_colspan')): ?>
      <td>
        <?php $label = $field->renderLabel() ?>
        <?php if (strpos($label, '*')): ?>
          <strong><?= $label ?></strong>
        <?php else: ?>
          <?= $label ?>
        <?php endif ?>
      </td>
      <td class="field">
    <?php else: ?>
      <td class="field colspan2" colspan="2">
    <?php endif ?>

      <?php if (!$widget->getAttribute('need_colspan')): ?>

        <?= $field->renderError() ?>
        <?= $field ?>

        <?php if ($field_help = $form->getWidgetSchema()->getHelp($field_name)): ?>
        <span class="field-help">
          <?= $field_help ?>
        </span>
        <?php endif ?>

      <?php else: ?>

        <b><?= $field->renderLabel() ?></b><br />
        <?= $field->renderError() ?>
        <?= $field ?><br />

        <?php if ($field_help = $form->getWidgetSchema()->getHelp($field_name)): ?>
          <span class="field-help">
            <?= $field_help ?>
          </span>
        <?php endif ?>
      <?php endif ?>
    </td>
  </tr>
  <?php if ($widget->getAttribute('show_additional_header')): ?>
    <tr>
      <td colspan="2">
        <br />
        <b>Дополнительные параметры</b>
      </td>
    </tr>
  <?php endif ?>
<?php endforeach ?>