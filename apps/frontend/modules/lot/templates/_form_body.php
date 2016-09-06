<?php sfConfig::set('show_rating_sidebar', true) ?>
<?php $i = 1 ?>
<?php foreach($form as $field_name => $field): ?>
<?php if (in_array($field_name, array('field69', 'field68'))) continue; ?>

<?php //Custom entities tpl
$sfn = substr($field_name, 0, strlen($field_name)-1);
if(in_array($sfn, DynamicForm::$custom_entities)): 
  switch ($field_name) {
    case 'flats':
      $sft = 'Квартиры в этом доме';
    break;
    case 'cottages':
      $sft = 'Коттеджи в этом поселке';
    break;
    case 'townhouses':
      $sft = 'Таунхаусы в этом поселке';
    break;
  } ?>
  <?php include_partial('entities', array('form' => $form, 'title' => $sft, 'section' => $sfn)); continue; ?>
<?php endif; ?>

  <?php $widget = $field->getWidget()  ?>
  <?php if($widget->getOption('type') == 'hidden'): ?>
    <?php if ($field_name == 'organization_contact_title'): ?>
      <tr><td colspan="2"><br /><b>Дополнительная контактная информация для объявления</b></td></tr>
    <?php else: ?>
      <?= $field ?>
    <?php endif ?>
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
        
        <?php if ($field_name == 'field53'): ?>
          <?= $field->renderError() ?>
          <?= $field ?>
          <?= $form['field69'] ?>
        <?php elseif (($field_name == 'field16') && ($type == 'apartament-rent')): ?>
          <?= $field->renderError() ?>
          <?= $field ?>
          <?= $form['field68'] ?>
        <?php else: ?>
          <?= $field->renderError() ?>
          <?= $field ?>
        <?php endif; ?>

        <?php if ($field_help = $form->getWidgetSchema()->getHelp($field_name)): ?>
        <span class="field-help">
          <?= $field_help ?>
        </span>
        <?php endif ?>

      <?php else: ?>

        <?php if ($widget->getOption('type') == 'checkbox'): ?>
          <?= $field->renderError() ?>
            <?= $field ?> <?= $field->renderLabel() ?>
        <?php elseif ($widget instanceOf sfWidgetFormGMap): ?>
          <span class="field-help"><?= $field->renderLabel() ?></span><br />
          <?= $field->renderError() ?>
          <?= $field ?><br />
        <?php else: ?>
          <b><?= $field->renderLabel() ?></b><br />
          <?= $field->renderError() ?>
          <?= $field ?><br />
        <?php endif ?>

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
