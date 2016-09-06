<div class="rc-box themes events">
  <div class="rc t"><div></div></div>
  <div class="content">
    <div class="col-left">
      <h2><span class="png24"></span>Блоги по темам</h2>
      <?php $current_theme = false ?>
      <?php if (count($themes)): ?>
        <ul>
        <?php $translit_tbl = DomusSearchRoute::$translit_table ?>
        <?php foreach ($themes as $theme_id => $theme): ?>
          <?php if (isset($article_themes)): ?>
            <?php if (array_key_exists($theme_id, $article_themes)): ?>
              <?php $current_theme = true ?>
            <?php else: ?>
              <?php $current_theme = false ?>
            <?php endif ?>
          <?php endif ?>
          <?php $url = str_replace(array_keys($translit_tbl), array_values($translit_tbl), $theme)?>
            <li>
              <?php if ($current_theme): ?>
              <strong><?= link_to($theme, '@blog_theme?theme=' . $url) ?></strong>
              <?php else: ?>
                <?= link_to($theme, '@blog_theme?theme=' . $url) ?>
              <?php endif ?>
            </li>
        <?php endforeach ?>
        </ul>
      <?php endif ?>
    </div>
  </div>
  <div class="rc b"><div></div></div>
</div>