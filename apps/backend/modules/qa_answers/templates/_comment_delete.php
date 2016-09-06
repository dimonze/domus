<?= (!$post_comment->deleted) ? link_to('удалить', 'comments/ListCommentDelete?id='.$post_comment->id) : '' ?>
