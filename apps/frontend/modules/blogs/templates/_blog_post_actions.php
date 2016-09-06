<ul class="blog-post-actions actionList">
  <li><a href="<?= url_for('@blog_post_edit?blog_url=' . $article->Blog->url . '&id=' . $article->id) ?>" class="blog-post-edit-link edit" title="редактировать" >редактировать</a></li>
  <li><a href="<?= url_for('@blog_post_delete?blog_url=' . $article->Blog->url . '&id=' . $article->id) ?>" class="blog-post-delete delit" title="удалить" >удалить</a></li>
<?php if ('publish' == $article->status || 'not_publish' == $article->status): ?>
  <li><a href="<?= url_for('@blog_post_publish?blog_url=' . $article->Blog->url . '&id=' . $article->id) ?>" class="blog-post-publish-link none" title="<?= ('publish' == $article->status) ? 'не показавать' : 'опубликовать' ?>">
  <?php if ('publish' == $article->status)
    echo 'не показавать'; else
    echo 'опубликовать'; ?>
  </a></li>
<?php elseif ('restricted' == $article->status): ?>
    <li>отказано в размещении</li>
<?php elseif ('moderate' == $article->status): ?>
      <li>на модерации</li>
<?php endif ?>
      
</ul><br /><br />

