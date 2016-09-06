<?php $routing = sfContext::getInstance()->getRouting() ?>
<?php $rule = $routing->getCurrentRouteName() ?>
<ul class="top-nav">
  <?php if (sfConfig::get('is_new_building')) include_partial('menu/new_building') ?>
  <?php if (sfConfig::get('is_cottage')) include_partial('menu/cottage') ?>

    <li <?= (preg_match('/post_type=article/', $routing->getCurrentInternalUri())) ? 'class="current"': '' ?>>
      <?= link_to('<em>Статьи</em>', '@posts?post_type=article') ?>
    </li>
    <li <?= (preg_match('/post_type=analytics/', $routing->getCurrentInternalUri())) ? 'class="current"': '' ?>>
      <?= link_to('<em>Аналитика</em>', '@posts?post_type=analytics') ?>
    </li>
    <?php if (preg_match('/^author_article/', $routing->getCurrentInternalUri()) ||
              preg_match('/^expert_article/', $routing->getCurrentInternalUri())): ?>
      <?php $class = 'current' ?>
    <?php else: ?>
      <?php $class = '' ?>
    <?php endif ?>
    <?php if (preg_match('/^user\/rating/', $routing->getCurrentInternalUri())): ?>
      <?php $class = 'current' ?>
    <?php else: ?>
      <?php $class = '' ?>
    <?php endif ?>
    <?php if (preg_match('/info/', $routing->getCurrentInternalUri())
          || preg_match('/blog/', $routing->getCurrentInternalUri())
          || preg_match('/agencies/', $routing->getCurrentInternalUri())): ?>
      <?php $class = 'current' ?>
    <?php else: ?>
      <?php $class = '' ?>
    <?php endif ?>
    <li class="inner <?= ($rule == 'news_by_section' || $rule == 'news_show' || $rule == 'news_by_theme') ? 'current' : ''?>">
    <a href="#"><em><span>Новости <var></var></span></em></a>
      <div class="dropbox">
        <div class="dropbox-wrap">
          <ul>
            <?php foreach (Post::$sections as $section => $title): ?>
              <li><?= link_to($title, '@news_by_section?news_section=' . $section)?></li>
            <?php endforeach?>
          </ul>
        </div>
      </div>
    </li>
    <li class="inner <?= $class ?>"><a href="#"><em><span>Еще <var></var></span></em></a>
      <div class="dropbox">
        <div class="dropbox-wrap">
          <ul>
            <li <?= (preg_match('/post_type=events/', $routing->getCurrentInternalUri())) ? 'class="current"': '' ?>>
              <?= link_to('События', '@posts?post_type=events') ?>
            </li>
            <li><?= link_to('Экспертные мнения', '@expert_article')?></li>
            <li><?= link_to('Авторские колонки', '@author_article')?></li>
            <li><?= link_to('Вопрос&amp;Ответ', '@qa') ?></li>
            <li><?= link_to('Рейтинги компаний', '@rating?type=company') ?></li>
            <li><?= link_to('Рейтинги риэлторов', '@rating?type=realtor') ?></li>
            <li><?= link_to('Словарь недвижимости', '/slovar_nedvizhimosti') ?></li>
            <li><?= link_to('Опросы', 'questionnaire') ?></li>
            <li><?= link_to('Блоги', '@blogs')?></li>
            <li><?= link_to('О Место.ру', '/info') ?></li>
            <li><?= link_to('Реклама', '/price') ?></li>            
          </ul>
        </div>
      </div>
    </li>

   <?php if ($sf_user->isAuthenticated()): ?>
   <?php 
      if (mb_strlen($sf_user->name) > 20) {
        $name = mb_substr($sf_user->name, 0, 20) . '...';
      }
      else {
        $name = $sf_user->name;
      }
   ?>
      <li class="inner <?= $class ?>">
        <a href="#" class="userName"><?= $name ?><var></var></a>
        <div class="dropbox">
          <div class="dropbox-wrap">
            <?php include_component('user', 'submenu') ?>
          </div>
        </div>        
      </li>
    <?php else: ?>
      <li>
        <?= link_to('Регистрация', 'user/register', 'class=enter popup rel=reg p=register') ?>
      </li>
      <li>
        <?= link_to('Вход', 'user/login', 'class=enter popup rel=loginwindow p=login') ?>
      </li>
    <?php endif ?> 
    <li class="last-menu">
      <?php $url = 'user/login' ?>
      <?php $rel = 'loginwindow' ?>
      <?php if ($sf_user->isAuthenticated()): ?>
        <?= link_to('Подать объявление бесплатно<span></span>', 'lot/add', 'class=new-insertionTop') ?>
      <?php else: ?>
        <?= link_to('Подать объявление бесплатно<span></span>', $url . '?forward=' . url_for('lot/add'), 'class="popup new-insertionTop" rel=' . $rel) ?>
      <?php endif ?>
    </li>
  </ul>

<div class="free-consult<?= sfContext::getInstance()->getRequest()->getCookie('uglybutton') == 'show' ? '' : ' free-consult-short' ?>" style="display:none">
  <a href="<?= url_for('form/consult') ?>" class="popup inner" rel="reg"></a><a class="close" href="#"></a>
</div>