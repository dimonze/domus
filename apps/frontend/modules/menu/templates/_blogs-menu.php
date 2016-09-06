<div class="nav-tree">
  <?= link_to('Главная', '@homepage')?>
  <span class="slash">/</span>&nbsp;
  <?= ('index' == $action) ? 'Блоги' : link_to('Блоги', '@blogs')?>
  <?= ('theme' == $action) ? '<span class="slash">/</span>&nbsp;'.$theme->title : ''; ?>
  <?= ('my' == $action) ? '<span class="slash">/</span>&nbsp;Мой блог' : ''; ?>
  <?= ('add' == $action) ? '<span class="slash">/</span>&nbsp;Добавление новой записи в блог' : ''; ?>
  <?= ('editpost' == $action) ? '<span class="slash">/</span>&nbsp;Редактирование записи в блоге' : ''; ?>
  <?= ('show' == $action) ? '<span class="slash">/</span>&nbsp;'.$blog->title : ''; ?>
  <?= 
    ('showpost' == $action) ?
      '<span class="slash">/</span>&nbsp;'
        . link_to($post->Blog->title, 'blog_show', array('blog_url' => $post->Blog->url))
        . '<span class="slash">/</span>&nbsp;' . $post->title :
      ''
  ?>
</div>