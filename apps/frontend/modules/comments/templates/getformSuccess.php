<?php if (!empty($form)): ?>
  <div id="comment-pop-up" class="pop-up-box">
    <a href="#" class="dashed pop-up-close-wrp">Скрыть<span class="pop-up-close png24"></span></a>
    <form action="<?= url_for('comments/add')?>" method="post" class="pop-up-content">
      <h3>
        Добавление <?= ($post_type == 'qa') ? 'ответа' : 'комментария' ?></h3>
      <input type="hidden" name="comment[parent_id]" value="<?= $parent_id ?>" />
      <input type="hidden" name="comment[post_id]" value="<?= $post_id ?>" />
      <input type="hidden" name="post_type" value="<?= $post_type ?>" />
      <?php if (!$sf_user->isAuthenticated()): ?>
        <div class="comment-form-span">
          <?= $form['user_name']->renderLabel() ?>
          <?= $form['user_name'] ?>
        </div>
        <div class="comment-form-span">
          <?= $form['user_email']->renderLabel() ?>
          <?= $form['user_email'] ?>
        </div>
      <?php endif ?>
      <div class="comment-form-span">
        <?= $form['body']->renderLabel() ?>
        <?= $form['body'] ?>
      </div>
      <?php if (!$sf_user->isAuthenticated()): ?>
        <div class="comment-form-span">
          <?= $form['captcha']->renderLabel() ?>
          <?= $form['captcha'] ?>
        </div>
      <?php endif ?>
      <a href="#" class="green-button add-q-btn">
        Добавить <?= ($post_type == 'qa') ? 'ответ' : 'комментарий' ?><span></span>
      </a>
    </form>
  </div>
<?php endif ?>