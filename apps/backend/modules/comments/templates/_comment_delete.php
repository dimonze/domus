<?= (!$post_comment->deleted) ? link_to('скрыть', 'comments/ListCommentDelete?id='.$post_comment->id) : '' ?>
<br />
<?= link_to('удалить', 'comments/ListCommentKill?id='.$post_comment->id) ?>
