<?php include_partial('menu/blogs-menu', array('action' => $action, 'blog' => $blog)) ?>

<div id="content">

    <div class="rc-box news authors-main-box">
      <div class="rc t"><div></div></div>
      <div class="content">
        <div class="authors-main-wrp">
        	<h2>Блог <?= $blog->title ?><br />
          <?php if ($user->company_name): ?>
            <div class="author-comm">
              <?= $user->company_name ?>
            </div>
          <?php endif ?>
        	</h2>
        	<div class="author-info">
            <?= image_tag(photo($user)) ?>
            <?php if ($user->Info->about): ?>
              <?= $user->Info->about ?>
            <?php endif ?>
        	</div>

        <?php if ($pager->getNbResults()): ?>
          <?php foreach ($pager->getResults() as $article): ?>
            <div class="item">
              <?php  include_partial('blogs/post_date_and_thems', array('post' => $article)); ?>
              <h4><?= link_to($article->title, '@blog_post_show?blog_url=' . $article->Blog->url . '&id=' . $article->id) ?></h4>
              <?= $article->lid ?>
            </div>
          <?php endforeach ?>
          <?php include_partial('global/posts-paginator', array('pager' => $pager))?>
        <?php endif ?>
      </div>

      </div><!-- .content -->
      <div class="rc b"><div></div></div>
    </div><!-- .rc-box + .authors-box -->


      <div class="rc b"><div></div></div>
      <?php cached_component('expert_article', 'list', null, $cache_prefix, 1900) ?>
      <?php include_partial('banner/block5-down-spec') ?>
    </div>


<div id="aside">
  <?php include_partial('page/aside-head')?>
  <?php cached_component('blogs', 'list', null, $cache_prefix, 1500) ?>
  <?php if (count($themes)): ?>
     <?php include_partial('blogs/blogthemes', array('themes' => $themes)) ?>
  <?php endif ?>
  <?php include_partial('banner/block3-right-spec') ?>
  <?php include_component('page', 'aside'); ?>
</div>