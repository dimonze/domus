<?php use_helper('Tag')?>

  <div class="rc-box box events">
    <div class="rc t"><div></div></div>
    <div class="content">
  <h3><?= link_to('<span class="png24"></span>' . $type_name, '@posts?post_type=' . $post_type) ?></h3>
  <?php if (count($posts) > 0): ?>
    <?php foreach($posts as $i => $post): ?>
    <div class="item">
      <?php if (0 === $i): ?>
        <?php $size = array(281, 209) ?>
        <?php $image_class = '' ?>
      <?php else: ?>
        <?php $size = array(96, 66) ?>
        <?php $image_class = 'thumb' ?>
      <?php endif ?>

      <?php if ($photo = photo($post, $size[0], $size[1])): ?>
        <?= link_to(
          image_tag($photo, array('class' => $image_class)),
          '@post_show?id=' . $post->id . '&post_type=' . $post_type
        ) ?>
      <?php endif ?>

      <?php if ($post_type == 'events'): ?>
        <h5><?= format_date($post->created_at, 'd MMMM yyyy')?></h5>
      <?php else: ?>
        <h6><?= format_date($post->created_at, 'd MMMM yyyy')?></h6>
      <?php endif ?>
      <h4><?= link_to($post->title, '@post_show?id=' . $post->id . '&post_type=' . $post_type)?></h4>
      <?php if (0 === $i): ?>
        <?= $post->lid ?>
      <?php endif ?>
    </div>
    <?php endforeach ?>
  <?php endif ?>
    </div>
    <?php $name = sprintf('%s %s', 'Все', mb_strtolower($type_name, 'UTF-8'))?>
  <?= link_to($name, '@posts?post_type=' . $post_type, array('class' => 'bottom-link') ) ?>
  <div class="rc b"><div></div></div>
</div>