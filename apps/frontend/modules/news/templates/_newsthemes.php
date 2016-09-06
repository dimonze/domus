<div class="rc-box news themes blue-box">
  <div class="rc t"><div></div></div>
  <div class="content">
    <div class="col-left">
      <h2><span class="png24"></span>Темы</h2>
      <?php $current_theme = false ?>
      <?php if (!isset($created_at)): ?>
        <?php $created_at = date('Y-m-d') ?>
      <?php endif ?>
      <?php if (count($themes)): ?>
        <ul>
        <?php $translit_tbl = DomusSearchRoute::$translit_table ?>
        <?php foreach ($themes as $theme_id => $theme): ?>
          <?php if (isset($news_themes)): ?>
            <?php if (array_key_exists($theme_id, $news_themes)): ?>
              <?php $current_theme = true ?>
            <?php else: ?>
              <?php $current_theme = false ?>
            <?php endif ?>
          <?php endif ?>
          <?php $url = str_replace(array_keys($translit_tbl), array_values($translit_tbl), $theme)?>          
          <li>
            <?php if ($current_theme): ?>
              <strong><?= link_to($theme, '@news_by_theme?news_section=' . $news_section . '&theme=' . $url . '&created_at=' . $created_at) ?></strong>
            <?php else: ?>
              <?= link_to($theme, '@news_by_theme?news_section=' . $news_section . '&theme=' . $url . '&created_at=' . $created_at) ?>
            <?php endif ?>
          </li>
        <?php endforeach ?>
        </ul>
      <?php endif ?>
    </div>
  </div>
  <div class="rc b"><div></div></div>
</div>