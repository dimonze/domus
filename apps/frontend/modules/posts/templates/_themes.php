<?php if (isset($posts) && count($posts) > 0): ?>
  <?php if (!($theme_count%2)): ?>
    <div class="rc-box events header-bl-box">
  <?php else: ?>
    <div class="rc-box events header-bl-box header-oth-box">
  <?php endif ?>
    <div class="rc t"><div></div></div>
    <div class="content">
      <?php $translit_tbl = DomusSearchRoute::$translit_table ?>
      <?php $theme_url = str_replace(array_keys($translit_tbl), array_values($translit_tbl), $theme)?>
      <h2><?= link_to($theme, '@posts_by_theme?post_type=' . $post_type .'&theme=' . $theme_url . '&page=1') ?></h2>
      <?php if (count($posts) > 0): ?>
        <?php foreach ($posts as $post): ?>
          <div class="item">
            <?php if ($photo = photo($post, 193, 128)): ?>
              <?php if(empty($post->slug)): //TODO Remove when slugs for all posts will be updated ?>
              <?= link_to(image_tag($photo), '@post_show?id=' . $post->id . '&post_type=' . $post->post_type) ?>
              <?php else: ?>
              <?= link_to(image_tag($photo), '@post_show_slug?slug=' . $post->slug.'-'.$post->id . '&post_type=' . $post->post_type) ?>
              <?php endif ?>
            <?php endif ?>

            <h6><?= format_date($post->created_at, 'd MMMM yyyy')?></h6>
            <h4><?php if(empty($post->slug)): //TODO Remove when slugs for all posts will be updated ?>
              <?= link_to($post->title, '@post_show?id=' . $post->id . '&post_type=' . $post->post_type)?>
            <?php else: ?>
              <?= link_to($post->title, '@post_show_slug?slug=' . $post->slug.'-'.$post->id . '&post_type=' . $post->post_type) ?>
            <?php endif ?></h4>
            <?= $post->lid ?>
          </div>
        <?php endforeach ?>
      <?php endif ?>
    </div>
    <div class="rc b"><div></div></div>
  </div>
<?php endif ?>