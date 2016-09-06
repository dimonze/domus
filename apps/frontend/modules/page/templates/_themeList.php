<div class="rc-box <?= $blue_bg ? 'news themes blue-box' : 'themes events' ?>">
  <div class="rc t"><div></div></div>
  <div class="content">
    <div class="col-left">
      <h2><span class="png24"></span>Темы</h2>
        <ul>
        <?php $translit_tbl = DomusSearchRoute::$translit_table ?>
        <?php foreach ($themes as $theme): ?>
          <?php $current_theme = ($theme->id == $current) ? true : false ?>
          <?php $url = str_replace(array_keys($translit_tbl), array_values($translit_tbl), $theme->title)?>
          <li>
            <?= link_to_if(
              (!$current_theme && $theme->hasActivePosts($type)),
              $current_theme ? '<strong>' . $theme . '</strong>' : $theme,
              '@'.$route.'?theme='.$url.$url_prefix
            ); ?>
          </li>
        <?php endforeach ?>
        </ul>
    </div>
  </div>
  <div class="rc b"><div></div></div>
</div>
