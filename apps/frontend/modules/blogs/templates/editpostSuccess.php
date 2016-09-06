<?php include_partial('menu/blogs-menu', array('action' => $action)) ?>
<?php use_javascript('/sfFormExtraPlugin/js/tiny_mce/tiny_mce') ?>
<?php if($sf_user->hasFlash('success')): ?><div class="flash success"><?= $sf_user->getFlash('success') ?></div><?php endif ?>
<div id="content">
  <div class="rc-box qw-box">
    <div class="rc t"><div></div></div>
    <div class="content">

      <form action="<?= url_for('@blog_post_edit?blog_url='.$sf_user->Blog->url.'&id='.$post->id) ?>" method="post" class="">
        <?= $form['id'] ?>
        <h2 class="qw-h2-in">Добавление новой записи в блог</h2>
          <div class="row">
            <label for="add_q_theme">Тема:</label>
            <div class="st">
            <?= $form['theme_id'] ?>
          </div>

        </div>
        <div class="row">
          <label for="add_q_title">Заголовок:</label>
          <div class="st st-wide"><?= $form['title'] ?></div>
        </div>
        <div class="row2 textarea-wide">
          <label for="add_q_text">Краткое описание:</label>
          <div class="st">
            <?= $form['lid'] ?>
          </div>
        </div>
        <div class="row2 textarea-wide">
          <label for="add_q_text">Текст сообщения:</label>
          <div class="st">
            <?= $form['body'] ?>
          </div>
        </div>

        <div class="row">
              <a href="#" class="green-button add-q-btn form-submit-button">Сохранить изменения<span></span></a>
            </div>

          </form>


        </div><!-- .content -->
        <div class="rc b"><div></div></div>
      </div><!-- .rc-box + .qw-box -->


              </div><!-- #content -->

              <div id="aside">
                <?php include_partial('page/aside-head')?>
  <?php include_component('menu', 'user') ?>
</div>