<?php include_partial('menu/blogs-menu', array('action' => $action)) ?>
<div id="content">

  <div class="rc-box news authors-list-box">
  <div class="rc t"><div></div></div>

  <div class="content">
    <h2><span></span>Блоги</h2>
    <div id="sort-by" class="sort-by">
        <?php if (isset($sort_order_date)):?>
          <?php if ($sort_order_date == 'asc'): ?>
            <?= link_to(
              'По дате<span class="dwn-arr">&darr;</span>',
              '@blogs?sort_order=created_at',
              array('style' => 'color:#090'))?>
          <?php else: ?>
            <?= link_to(
              'По дате<span class="up-arr">&uarr;</span>',
              '@blogs?&sort_order=created_at-desc',
              array('style' => 'color:#090'))?>
          <?php endif ?>
        <?php else: ?>
          <?= link_to('По дате<span class="up-arr">&uarr;</span>', '@blogs?&sort_order=created_at-desc')?>
        <?php endif ?>
        <?php if (isset($sort_order_author)):?>
          <?php if ($sort_order_author == 'asc'): ?>
            <?= link_to(
              'По автору<span class="dwn-arr">&darr;</span>',
              '@blogs?&sort_order=created_at-desc',
              array('style' => 'color:#090'))?>
          <?php else: ?>
            <?= link_to(
              'По автору<span class="up-arr">&uarr;</span>',
              '@blogs?&sort_order=author-desc',
              array('style' => 'color:#090'))?>
          <?php endif ?>
        <?php else: ?>
          <?= link_to(
            'По автору<span class="up-arr">&uarr;</span>',
            '@blogs?&sort_order=author-desc')?>
        <?php endif ?>
    </div>
      <?php if ($pager->getNbResults()): ?>
        <?php foreach ($pager->getResults() as $id => $blog): ?>
          <div class="authors-list-item <?= ($pager->getNbResults() == (int) $id + 1) ? 'last-item' : ''?>">
            <div class="author-info">
              <?php $user = $blog->User; ?>
              <h4><?= $user->name?></h4>
              <?php if ($user->company_name): ?>
                <div class="author-comm">
                  <?= $user->company_name ?>
                </div>
              <?php endif ?>

              <?= image_tag(photo($user)) ?>

              <?php if ($user->Info->about): ?>
                <?= $user->Info->about ?>
              <?php endif ?>
            </div>

            <?php if ($blog->LatestPosts): ?>
              <?php foreach($blog->LatestPosts as $post): ?>
                <div class="item">
                  <?php include_partial('blogs/post_date_and_thems', array('post' => $post)) ?>
                  <h4><?= link_to($post->title, '@blog_post_show?blog_url=' . $blog->url . '&id=' . $post->id) ?></h4>
                  <?= $post->lid ?>
                </div>
              <?php endforeach ?>
            <?php endif ?>
          </div>
        <?php endforeach ?>
    <?php include_partial('global/posts-paginator', array('pager' => $pager)) ?>
  <?php endif ?>
  </div><!-- .content -->

  <div class="rc b"><div></div></div>
</div><!-- .rc-box + .authors-box -->

  <div class="index-adv-teaser">
    <div class="wrap closer">
      <?php include_partial('banner/blogs_before_experts') ?>
    </div>
  </div>

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