<?php use_helper('I18N', 'Date') ?>
<?php include_partial('blogpost/assets') ?>

<div id="sf_admin_container">
  <h1>
<?php
  $attrHolder = $sf_user->getAttributeHolder()->getAll('admin_module');
  $blog_id = $attrHolder['blogpost.filters']['blog_id'] ? (int)$attrHolder['blogpost.filters']['blog_id'] : false;
?>
<?php echo __('Записи в блоге %%blog_title%%',
        array('%%blog_title%%' => get_partial('blogpost/blog_title', array(
            'blog_id' => $blog_id
          ))
        ),
        'messages') ?>
  </h1>


  <?php include_partial('blogpost/flashes') ?>

  <div id="sf_admin_header">
    <?php include_partial('blogpost/list_header', array('pager' => $pager)) ?>
  </div>

  <div id="sf_admin_bar">
    <?php include_partial('blogpost/filters', array('form' => $filters, 'configuration' => $configuration)) ?>
  </div>

  <div id="sf_admin_content">
    <form action="<?php echo url_for('blog_post_blogpost_collection', array('action' => 'batch')) ?>" method="post">
    <?php include_partial('blogpost/list', array('pager' => $pager, 'sort' => $sort, 'helper' => $helper)) ?>
    <ul class="sf_admin_actions">
      <?php include_partial('blogpost/list_batch_actions', array('helper' => $helper)) ?>
      <?php include_partial('blogpost/list_actions', array('helper' => $helper)) ?>
    </ul>
    </form>
  </div>

  <div id="sf_admin_footer">
    <?php include_partial('blogpost/list_footer', array('pager' => $pager)) ?>
  </div>
</div>
