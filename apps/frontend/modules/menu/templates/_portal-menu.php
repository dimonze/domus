<div class="nav-tree">  
  <?= link_to('Главная', '@homepage')?>
  
  <?php if (isset($post_type) && (isset($theme) || isset($author) || isset($post_title) || isset($news_section))): ?>
    <span class="slash">/</span>&nbsp;
    <?php if (in_array($post_type, array('article', 'analytics', 'events'))): ?>
      <?= link_to(
        Post::$types[$post_type],
        sprintf('@posts?post_type=%s', $post_type)) ?>
    <?php else: ?>
      <?php if (isset($news_section)): ?>        
        <?= link_to(
          Post::$types[$post_type],
          sprintf(
            '@%s_by_section?news_section=%s',
            $post_type, $news_section)) ?>
      <?php else: ?>
      <?= link_to(
        Post::$types[$post_type],
        sprintf('@%s', $post_type)) ?>
      <?php endif ?>
    <?php endif ?>
  <?php else: ?>
    <span class="slash">/</span>&nbsp;
    <?= Post::$types[$post_type] ?>
  <?php endif ?>

  <?php if (isset($news_section)): ?>
    <span class="slash">/</span>&nbsp;    
    <?php if (isset($theme) || isset($post_title)): ?>
    <?= link_to(News::$sections[$news_section], sprintf(
      '@%s_by_section?news_section=%s',
      $post_type, $news_section)) ?>
    <?php else: ?>
      <?= News::$sections[$news_section] ?>
    <?php endif ?>
  <?php endif ?>

  <?php if (isset($author) && $author instanceOf PostAuthor): ?>
    <span class="slash">/</span>&nbsp;
    <?php if (!isset($post_title)): ?>
      <?= $author->name ?>
    <?php else: ?>
      <?= link_to(
        $author->name,
        sprintf('@%s_show_author?author_id=%d', $post_type, $author->id))?>
    <?php endif ?>
  <?php endif ?>

  <?php if (isset($theme) && $theme instanceOf Theme): ?>    
    <span class="slash">/</span>&nbsp;<?= $theme->title ?>
  <?php endif ?>
    
  <?php if (isset($post_title)): ?>
    <span class="slash">/</span>&nbsp;<?= $post_title ?>
  <?php endif ?>
</div>
<?php if (!empty($post_title)): ?>
  <div class="social">
    <script type="text/javascript" src="http://yandex.st/share/share.js" charset="utf-8"></script>
    <!--check code-->
    <div class="yashare-auto-init" data-yashareL10n="ru" data-yashareType="button" data-yashareQuickServices="vkontakte,facebook,lj,odnoklassniki,twitter"></div> 
  </div>
<?php endif ?>