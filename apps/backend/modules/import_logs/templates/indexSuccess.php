<?php use_helper('I18N', 'Date') ?>
<?php include_partial('import_logs/assets') ?>

<div id="sf_admin_container">
  <h1><?php echo __('Статистика импорта объявлений', array(), 'messages') ?></h1>

  <?php include_partial('import_logs/flashes') ?>

  <div id="sf_admin_header">
    <?php include_partial('import_logs/list_header', array('pager' => $pager)) ?>
  </div>

  <div id="sf_admin_bar" style="width: 100%;">
    <?php include_partial('import_logs/filters', array('form' => $filters, 'configuration' => $configuration)) ?>
  </div>

  <div id="sf_admin_content" style="width: 100%;">
    <form action="<?php echo url_for('import_logs_collection', array('action' => 'batch')) ?>" method="post">
      <?php include_partial('import_logs/list', array('pager' => $pager, 'sort' => $sort, 'helper' => $helper)) ?>
    </form>
  </div>

  <div id="sf_admin_footer">
    <?php include_partial('import_logs/list_footer', array('pager' => $pager)) ?>
  </div>
</div>