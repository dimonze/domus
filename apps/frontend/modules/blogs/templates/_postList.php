<div class="rc-box expert-opinion">
  <div class="rc t"><div></div></div>
  <div class="content">

    <h2><?= link_to('<span class="png24"></span>Блоги', '@blogs')?></h2>
    <?php if ($posts): ?>
      <?php $i = 0; ?>
      <?php foreach ($posts as $post): ?>
        <?php $i++ ?>
        <div class="bubble-wrp">
          <?= link_to(
            image_tag(photo($post->User, 50, 50), 'class=bubble-foto'),
            '@blog_post_show?blog_url=' . $post->Blog->url . '&id=' . $post->id
          ) ?>
          <div class="bubble3">
            <div class="tail-l png24"></div>
            <div class="tl bbl-corn png24"></div>
            <div class="tr bbl-corn png24"></div>
            <div class="bubble3-content">
              <h5><?= $post->User->name ?>, <strong><?= $post->User->getTypeText() ?>, <?= $post->User->company_name ?></strong></h5>
              <h4><?= link_to($post->title, '@blog_post_show?blog_url=' . $post->Blog->url . '&id=' . $post->id) ?></h4>
              <?= ('homepage' == $sf_context->getActionName() && $i != 1) ? '' : $post->lid ?>
            </div>
            <div class="bl bbl-corn png24"></div>
            <div class="br bbl-corn png24"></div>
          </div>
        </div>
      <?php endforeach ?>
    <?php endif ?>
  </div>
  <?= link_to('Все блоги', '@blogs', array('class' => 'bottom-link'))?>
  <div class="rc b"><div></div></div>
</div>