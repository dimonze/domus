<?php if(!$form->getObject()->isNew()): ?>
<input type="hidden" name="import_order[info]" value='<?= $form->getObject()->data['info'] ?>' id="import_order_data">
<?php $data_form = new ImportOrderDataForm(unserialize($form->getObject()->data['info'])); ?>
<?php foreach ($data_form as $field_id => $field): ?>
  <?php if ($field_id != 'i_agree'): ?>
    <div class="sf_admin_form_row sf_admin_text sf_admin_form_field_company_name">
      <div>
        <?= $field->renderLabel() ?>
        <div class="content">
          <?php if ($field_id == 'type'): ?>
            <?php foreach ($field->getValue() as $type): ?>
              <?= Lot::$type_ru[$type] ?><br/>
            <?php endforeach ?>  
          <?php else: ?>
            <?= $field->getValue() ?>
          <?php endif ?>
        </div>
      </div>
    </div>
  <?php endif ?>
<?php endforeach; ?>
<?php endif ?>
