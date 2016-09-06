<?php include_partial('menu/portal-menu', array('post_type' => 'author_article')) ?>
<div id="content">

  <div class="rc-box news authors-list-box">
  <div class="rc t"><div></div></div>

  <div class="content">
    <h2><span></span>Авторские колонки</h2>
    <div id="sort-by" class="sort-by">
        <?php if (isset($sort_order_date)):?>
          <?php if ($sort_order_date == 'asc'): ?>
            <?= link_to(
              'По дате<span class="dwn-arr">&darr;</span>',
              '@author_article?sort_order=created_at',
              array('style' => 'color:#090'))?>
          <?php else: ?>
            <?= link_to(
              'По дате<span class="up-arr">&uarr;</span>',
              '@author_article?sort_order=created_at-desc',
              array('style' => 'color:#090'))?>
          <?php endif ?>
        <?php else: ?>
          <?= link_to('По дате<span class="up-arr">&uarr;</span>', '@author_article?sort_order=created_at-desc')?>
        <?php endif ?>
        <?php if (isset($sort_order_author)):?>
          <?php if ($sort_order_author == 'asc'): ?>
            <?= link_to(
              'По автору<span class="dwn-arr">&darr;</span>',
              '@author_article?sort_order=author',
              array('style' => 'color:#090'))?>
          <?php else: ?>
            <?= link_to(
              'По автору<span class="up-arr">&uarr;</span>',
              '@author_article?sort_order=author-desc',
              array('style' => 'color:#090'))?>
          <?php endif ?>
        <?php else: ?>
          <?= link_to(
            'По автору<span class="up-arr">&uarr;</span>',
            '@author_article?sort_order=author-desc')?>
        <?php endif ?>
    </div>
      <?php if ($pager->getNbResults()): ?>
        <?php foreach ($pager->getResults() as $id => $author): ?>
          <div class="authors-list-item <?= ($pager->getNbResults() == (int) $id + 1) ? 'last-item' : ''?>">
            <div class="author-info">
              <?= image_tag(photo($author)) ?>
              <h4><?= $author->name?></h4>
                <?php if ($author->company): ?>
                  <div class="author-company">
                    <?php if ($author->post): ?>
                      <?= $author->post ?>,&nbsp;
                    <?php endif ?>
                    <?= $author->company ?>
                  </div>
              <?php endif ?>
              <?php if ($author->description): ?>
                <?= $author->description ?>
              <?php endif ?>

            </div>
            <?php if ($author->LatestPosts): ?>
              <?php foreach($author->LatestPosts as $article): ?>
                <div class="item">
                  <?php include_partial('posts/post_date_and_thems', array('post' => $article)) ?>
                  <h4><?php if(empty($article->slug)): //TODO Remove when slugs for all posts will be updated ?>
                    <?= link_to($article->title, '@author_article_show?author_id=' . $author->id . '&id=' . $article->id) ?>
                   <?php else: ?>
                    <?= link_to($article->title, '@author_article_show_slug?author_id=' . $author->id . '&slug=' . $article->slug.'-'.$article->id) ?>
                  <?php endif ?></h4>
                  <?= $article->lid ?>
                </div>
              <?php endforeach ?>
            <?php endif ?>
          </div>
          <?php if(($id + 1) % 5 == 0 && $pager->getNbResults() != (int) $id + 1 ): ?>
    </div>
          <div class="authors-list-item">
            <div class="index-adv-teaser">
              <div class="wrap closer">
                <?php include_partial('banner/authors_any5th') ?>
              </div>
            </div>
          </div>
    <div class="content">
          <?php endif ?>
        <?php endforeach ?>
    <?php include_partial('global/posts-paginator', array('pager' => $pager)) ?>
  <?php endif ?>
  </div><!-- .content -->

  <div class="rc b"><div></div></div>
</div><!-- .rc-box + .authors-box -->

  <?php cached_component('expert_article', 'list', null, $cache_prefix, 1900) ?>
  <?php include_partial('banner/block5-down-spec') ?>

</div>

<div id="aside">
  <?php include_partial('page/aside-head')?>
  <?php cached_component('author_article', 'authors', null, $cache_prefix, 1500) ?>
  <?php include_partial('global/aside_search')?>
  <?php
    cached_component('page', 'themeList', array(
      'type' => 'author_article',
      'current' => isset($current_theme) ? $current_theme : false
      ),
      $cache_prefix,
      1900
    )
  ?>
  <?php include_partial('banner/block3-right-spec') ?>
  <?php include_component('page', 'aside'); ?>
</div>