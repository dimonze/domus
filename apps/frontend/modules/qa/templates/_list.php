<div class="rc-box q-and-a">
  <div class="rc t"><div></div></div>
  <div class="content">
    <h2><?= link_to('<span class="png24"></span>Вопросы и ответы', '@qa')?></h2>
    <?php foreach ($qa as $q): ?>
      <div class="item">
        <div class="question">
          <a href="#"><?= link_to($q->title, 'qa_show', array('id' => $q->id)); ?></a>
        </div>
        <div class="b"></div>
        <div class="question-info">
          <div>
          <?php include_partial('posts/post_date_and_thems', array('post' => $q)); ?>
        </div>
        <span><?= count($q->Comments) . ' ' . ending(count($q->Comments), 'ответ', 'ответа', 'ответов') ?></span>
      </div>
    </div>
    <?php endforeach ?>
    <a class="button" href="<?= url_for('@qa_add') ?>"><span>Задать вопрос</span></a>
  </div>
  <a class="bottom-link" href="<?= url_for('@qa') ?>">Все вопросы и ответы</a>
  <div class="rc b"><div></div></div>
</div>