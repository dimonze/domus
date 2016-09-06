<?php use_helper('I18N', 'Date') ?>
<?php include_partial('global/assets') ?>

<div id="sf_admin_container">
  <?php if (isset($widgets)): ?>
    <h1>Форма <?php echo $type_names['accusative'] ?></h1>
  <?php else: ?>
    <h1>Формы:</h1>
    <ul>
    <?php foreach ($types as $type => $names): ?>
      <li><?php echo link_to($names['name'], 'form/index?type='.$type) ?></li>
    <?php endforeach ?>
    </ul>
  <?php endif ?>

  <?php include_partial('global/flashes') ?>

  <div id="sf_admin_content">
    <?php if (isset($widgets)): ?>
      <script type="text/javascript">
        window.widgets_in_use = <?php echo json_encode($widgets) ?>;
        window.form_save_url = '<?php echo url_for('form/save?type='.$sf_params->get('type')) ?>';
      </script>
      <div id="form">
        <h3>Форма</h3>

        <div class="block">Адрес объекта, расположение на карте</div>
        <div id="form-container" class="block editable"></div>
        <div class="block">Текстовое описание, фотографии, ссылки, дополнительная контактная информация</div>

        <ul class="sf_admin_actions form">
          <li class="sf_admin_action_save"><?php echo link_to('Сохранить', 'form/save') ?></li>
        </ul>

      </div>

      <?php include_component('form', 'widgets') ?>
    <?php endif ?>
  </div>

</div>
