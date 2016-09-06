<?php use_helper('I18N', 'Date') ?>
<?php include_partial('global/assets') ?>

<div id="sf_admin_container">
  <h1><?php echo __('', array(), 'messages') ?></h1>
  <?php if (isset($groups)): ?>
    <h1>Порядок отображения информации в карточке объекта - <?php echo $type_names['plural'] ?></h1>
  <?php else: ?>
    <h1>Порядок отображения информации в карточке объекта:</h1>
    <ul>
    <?php foreach ($types as $type => $names): ?>
      <li><?php echo link_to($names['name'], 'settings/lot-info-order?type='.$type) ?></li>
    <?php endforeach ?>
    </ul>
  <?php endif ?>

  <?php include_partial('global/flashes') ?>

  <div id="sf_admin_content">
    <?php if (isset($groups)): ?>
      <script type="text/javascript">
        window.groups_in_use = <?php echo json_encode($groups) ?>;
        window.order_save_url = '<?php echo url_for('settings/lot-info-order?type='.$sf_params->get('type')) ?>';
      </script>
      <div id="form">
        <h3>Группы полей</h3>

        <div id="group-container">
        </div>

        <ul class="sf_admin_actions order">
          <li class="sf_admin_action_new"><?php echo link_to('Добавить группу', 'settings/lotinfoorder') ?></li>
          <li class="sf_admin_action_save"><?php echo link_to('Сохранить', 'settings/lotinfoorder?save=true') ?></li>
        </ul>

      </div>

      <?php include_component('form', 'widgets') ?>
    <?php endif ?>
  </div>

</div>
