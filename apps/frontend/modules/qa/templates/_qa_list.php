<?php use_helper('Text', 'Escaping') ?>

<?php foreach ($qa_list as $question): ?>
<div class="bubble-wrp">
  <?= image_tag(photo($question->User, 50, 50), 'class=bubble-foto alt=') ?>

  <div class="bubble3">
    <div class="tail-l png24"></div>
    <div class="tl bbl-corn png24"></div><div class="tr bbl-corn png24"></div>
    <div class="bubble3-content">
        <h4><?= link_to($question->title, 'qa_show', array('id' => $question->id)); ?></a></h4>
      <p>
        <?= $question->post_text ?>
      </p>
      <h5 class="qw-info">
        <?= $question->author_name ? $question->author_name : $question->User->name ?>
        , <?= format_date($question->created_at, 'd MMMM, HH:mm')?>
      </h5>
        <?php if(count($themes = $question->Themes)): ?>
          <h5>
          <?php $i = 0; foreach($themes as $theme) { echo (0 == $i) ? $theme->title : ', '.$theme->title; $i++; } ?>
          </h5>
        <?php endif; ?>
      <div class="qw-actions">
        <a href="<?= url_for('qa/show?id='.$question->id); ?>" class="to-reply">Ответить<span></span></a>
        <a href="<?= url_for('qa/show?id='.$question->id); ?>" class="reply">
          <?= count($question->Comments).' '.ending(count($question->Comments), 'ответ', 'ответа', 'ответов') ?>
          <span></span>
        </a>
      </div>
    </div>
    <div class="bl bbl-corn png24"></div><div class="br bbl-corn png24"></div>
  </div>
</div>
<?php endforeach; ?>