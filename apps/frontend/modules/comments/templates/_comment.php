<?php use_helper('Text', 'Escaping') ?>
<div id="comment-<?= $comment->id ?>"class="bubble3" style="margin-left: <?= $comment->level * 10 ?>px;">
  <?php if ($comment->user_id): ?>
    <?= link_to(
        image_tag(photo($comment->User, 50, 50)),
        'user_card',
        $comment->User,
        'class=bubble-foto'
      ) ?>
  <?php else: ?>
    <?= image_tag_s('pict_user', array('class' => 'bubble-foto')) ?>
  <?php endif ?>

    <div class="tail-l png24"></div>
  <div class="tl bbl-corn png24"></div><div class="tr bbl-corn png24"></div>
  <div class="bubble3-content" rel="<?= $comment->id ?>">
    <?php if (!$comment->deleted): ?>
      <h5>
        <?=
          ($comment->User) ?
            link_to($comment->User->name, 'user_card', $comment->User) :
            $comment->user_name
        ?>, <strong><?= format_date($comment->created_at, 'd MMMM, HH:mm')?></strong>
      </h5>
      <span class="comment-body">
        <?= simple_format_text(strip_tags($comment->body)) ?>
        <a href="#" id="leave-a-comm" class="post-comm dashed">Ответить</a>
        <?php if ($sf_user->hasCredential('moder-portal_comments')): ?>
          <span class="delete-comment">
          <?=
            link_to(
              'Удалить',
              '@comments_actions?action=delete&id=' . $comment->id
            )
          ?></span>
        <?php endif ?>
      </span>
    <?php else: ?>
      <h5>
        <?=
          ($comment->User) ?
            link_to($comment->User->name, 'user_card', $comment->User) :
            $comment->user_name
        ?>,
        <strong>
          <?= format_date($comment->created_at, 'd MMMM, HH:mm')?>
        </strong>
      </h5>
      <span class="comment-body"><p><?= ($section == 'qa') ? 'Ответ' : 'Комментарий' ?> был удалён модератором</p></span>
    <?php endif ?>
  </div>
  <div class="bl bbl-corn png24"></div><div class="br bbl-corn png24"></div>
</div>
