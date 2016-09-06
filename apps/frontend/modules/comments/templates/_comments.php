<?php use_helper('Text', 'Escaping') ?>

<div class="comments" rel="<?= ($post instanceOf Post) ? $post->post_type : 'blog' ?>" value="<?= $post->id ?>">
  <?php if ($post instanceOf Post && $post->post_type != 'qa'): ?>
    <?php $string = ' комментар' . ending(count($comments), 'ий', 'ия', 'ев') ?>
  <?php else: ?>
    <?php $string = ' ответ' . ending(count($comments), '', 'а', 'ов') ?>
  <?php endif ?>
<span class="comments-count"><span class="png24"></span><?= count($comments) . $string ?></span>
  <?php if (($post instanceOf BlogPost) && !sfContext::getInstance()->getUser()->isAuthenticated()): ?>
  <?php else: ?>
    <a href="#" id="leave-a-comm" class="post-comm dashed">
      <?= ($post instanceOf Post && $post->post_type == 'qa') ? 'Ответить' : 'Комментировать' ?>
      <span class="png24"></span>
    </a>
  <?php endif ?>
  <div class="comments-wrp">
    <?php foreach ($comments as $id => $comment): ?>
      <?php if ($id == 0): ?>
        <?php $first_comment = true ?>
      <?php else: ?>
        <?php $first_comment = false?>
      <?php endif ?>
      <?php include_partial('comments/comment', array(
          'comment'       => $comment,
          'first_comment' => $first_comment,
          'section'       => ($post instanceOf Post) ? $post->post_type : 'author_article')) ?>
    <?php endforeach ?>
  </div><!-- .comments-wrp -->
</div>