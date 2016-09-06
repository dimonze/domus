<?php use_helper('I18N', 'Date') ?>
<?php include_partial('blog/assets') ?>

<div id="sf_admin_container">
  <h1><?php echo __('Блоги', array(), 'messages') ?></h1>

  <?php include_partial('blog/flashes') ?>

  <div id="sf_admin_header">
    <?php include_partial('blog/list_header', array('pager' => $pager)) ?>
  </div>

  <div id="sf_admin_bar">
    <?php include_partial('blog/filters', array('form' => $filters, 'configuration' => $configuration)) ?>
  </div>

  <div id="sf_admin_content">
    <form action="<?php echo url_for('blog_collection', array('action' => 'batch')) ?>" method="post">
    <?php include_partial('blog/list', array('pager' => $pager, 'sort' => $sort, 'helper' => $helper)) ?>
    <ul class="sf_admin_actions">
      <?php include_partial('blog/list_batch_actions', array('helper' => $helper)) ?>
      <?php include_partial('blog/list_actions', array('helper' => $helper)) ?>
    </ul>
    </form>
  </div>
  <form name="show-blog-posts"method="post" style="display: none;" action="/backend.php/blogpost/filter/action">
    <input type="hidden" name="blog_post_filters[blog_id]" value="<?= $blog->id ?>" />
  </form>
  <div id="sf_admin_footer">
    <?php include_partial('blog/list_footer', array('pager' => $pager)) ?>
  </div>
</div>
