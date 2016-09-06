<?php include_partial('menu/portal-menu', array(
  'post_type'   => $sf_user->getAttribute('post_type'),
  'post_title'  => 'Добавление нового вопроса'))?>

<div id="content">
  <div class="rc-box qw-box">
    <div class="rc t"><div></div></div>
    <div class="content">
      <form action="<?= url_for('qa/add') ?>" method="post" class="new-form ajax-validate register-form">
        <h2 class="qw-h2-in">Добавление нового вопроса</h2>
          <div class="row">
            <div class="fl">

              <label for="add_q_name">Ваше имя:</label>
              <div class="st"><?= $form['author_name'] ?></div>
            </div>
            <?php if (!$user->isAuthenticated()): ?>
            <div class="fr">
              <label for="add_q_mail">E-mail:</label>
              <div class="st"><?= $form['author_email'] ?></div>
            </div>
            <?php endif; ?>
          </div>

          <div class="row">
            <label for="add_q_theme">Тема:</label>
            <div class="st">
            <?= $form['themes_list'] ?>
          </div>

        </div>
        <div class="row">
          <label for="add_q_title">Заголовок:</label>
          <div class="st st-wide"><?= $form['title'] ?></div>
        </div>
        <div class="row2 textarea-wide">
          <label for="add_q_text">Текст сообщения:</label>
          <div class="st">
            <?= $form['post_text'] ?>
          </div>
        </div>

        <div class="row">
          <?php if (!$user->hasCredential('redactor-qa-actions')): ?>
          <?= $form['captcha'] ?>
              <!-- <div class="st captcha-inp"><input type="text" name="name" id="add_q_name" /></div> -->

          <?php endif; ?>
              <a href="#" class="green-button add-q-btn form-submit-button">Задать вопрос<span></span></a>
            </div>

          </form>


        </div><!-- .content -->
        <div class="rc b"><div></div></div>
      </div><!-- .rc-box + .qw-box -->
  <?php foreach ($pager->getResults() as $question): ?>
                <div class="rc-box qw-box">
                  <div class="rc t"><div></div></div>
                  <div class="content">
                    <h2 class="qw-h2-in"><?= format_date($question->created_at, 'd MMMM') ?></h2>
      <?php $date = format_date($question->created_at, 'yyyy-MM-dd') ?>
      <?php include_partial('qa/qa_list', array('qa_list' => $qa_dates[$date])) ?>
              </div><!-- .content -->
              <div class="rc b"><div></div></div>
            </div><!-- .rc-box + .qw-box -->
  <?php endforeach; ?>

  <?php cached_component('expert_article', 'list', null, $cache_prefix, 1900) ?>
  <?php cached_component('author_article', 'list', array('news' => true), $cache_prefix, 1700) ?>

              </div><!-- #content -->

<div id="aside">
  <?php include_partial('page/aside-head') ?>
  <?php
  cached_component('page', 'themeList', array(
    'type' => 'qa',
    'current' => isset($current_theme) ? $current_theme : false
    ),
    $cache_prefix,
    1900
    )
  ?>
  <?php include_component('page', 'aside') ?>
</div>