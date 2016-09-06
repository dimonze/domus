<?php use_javascript('/sfFormExtraPlugin/js/tiny_mce/tiny_mce') ?>
<?php include_partial('menu/blogs-menu', array('action' => $action)) ?>

<div id="content">
  <div class="rc-box qw-box">
    <div class="rc t"><div></div></div>
    <div class="content">
      <form action="<?= url_for('@blog_post_add') ?>" method="post" class="new-form">
        <h2 class="qw-h2-in">Добавление новой записи в блог</h2>
          <div class="row">
            <div class="fl">
            <label for="add_q_theme">Тема:</label>
            <div class="st"><?= $form['theme_id'] ?></div>
              </div>
          </div>


        <div class="row">
          <label for="add_q_title">Заголовок:</label>
          <div class="st st-wide"><?= $form['title'] ?></div>
          <?= $form['title']->renderError() ?>
        </div>
        <div class="row2 textarea-wide">
          <label for="add_q_text">Краткое описание:</label>
          <div class="st">
            <?= $form['lid'] ?>
          </div>
          <?= $form['lid']->renderError() ?>
        </div>
        <div class="row2 textarea-wide">
          <label for="add_q_text">Текст сообщения:</label>
          <div class="st">
            <?= $form['body'] ?>
          </div>
        </div>

        <div class="row">
              <a href="#" class="green-button add-q-btn form-submit-button">Добавить<span></span></a>
            </div>

          </form>


        </div><!-- .content -->
        <div class="rc b"><div></div></div>
      </div><!-- .rc-box + .qw-box -->

  <?php cached_component('expert_article', 'list', null, $cache_prefix, 1900) ?>
  <?php cached_component('author_article', 'list', array('news' => true), $cache_prefix, 1700) ?>

              </div><!-- #content -->

              <div id="aside">
                <?php include_partial('page/aside-head')?>
  <?php if (count($themes)): ?>
  <?php include_partial('blogs/blogthemes', array('themes' => $themes)) ?>
  <?php endif ?>
  <?php cached_component('news', 'listforposts', null, $cache_prefix, 1200) ?>
                  <div class="articles-analytics-box">
                    <div class="wrap">
      <?php cached_component('posts', 'list', array('post_type' => 'events'), $cache_prefix, 1400) ?>
                </div>
                <div class="b"></div>
              </div>
              <div class="articles-analytics-box">
                <div class="wrap">
      <?php cached_component('posts', 'list', array('post_type' => 'analytics'), $cache_prefix, 1200) ?>
    </div>
    <div class="b"></div>
  </div>
<?php cached_component('qa', 'list', array('post_type' => 'qa'), $cache_prefix, 1200) ?>
</div>