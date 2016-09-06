<div class="col side">
  <?php if (sfConfig::get('lot_noindex')): ?>
    <noindex>
  <?php endif ?>
  <p><?= str_replace('%год%', date('Y'), sfConfig::get('app_layout_copyright')) ?></p>
  <?php if (sfConfig::get('lot_noindex')): ?>
    </noindex>
  <?php endif ?>
  <ul>
    <li>
        <?php if (sfConfig::get('lot_noindex')): ?>
          <noindex>
        <?php endif ?>
        <?= link_to('Контактная информация', '/contacts') ?>
        <?php if (sfConfig::get('lot_noindex')): ?>
          </noindex>
        <?php endif ?>
    </li>
    <?php if (sfConfig::has('app_layout_rambler_top100')): ?>
      <li>
        <?php if (sfConfig::get('lot_noindex')): ?>
          <noindex>
        <?php endif ?>
        <?= sfConfig::get('app_layout_rambler_top100')?>
        <?php if (sfConfig::get('lot_noindex')): ?>
          </noindex>
        <?php endif ?>
      </li>
    <?php endif ?>
  </ul>
</div>

<?php if (sfConfig::get('lot_noindex')): ?>
  <noindex>
<?php endif ?>
<div class="col info">
  <h3><span></span>Информация</h3>
  <ul>
    <?php if (count($pages)): ?>
      <?php foreach ($pages as $page): ?>
        <li>
          <?php if ($page->url == 'price'): ?>
            <?= link_to(
              str_replace(' ', '&nbsp;', "<strong>" . $page->name . "</strong>"),
              '/' . $page->url
            ) ?>
          <?php else: ?>
            <?= link_to(str_replace(' ', '&nbsp;', $page->name), '/' . $page->url) ?>
          <?php endif ?>
        </li>

      <?php endforeach ?>
    <?php endif ?>
  </ul>
</div>

<div class="col text">
  <h3><span></span>Тексты</h3>
  <ul>
    <li><?= link_to('Новости рынка', '@news_by_section?news_section=news-market') ?></li>
    <li><?= link_to('Статьи', '@posts?post_type=article') ?></li>
    <li><?= link_to('Аналитика', '@posts?post_type=analytics') ?></li>
    <li><?= link_to('Авторские колонки', '@author_article')?></li>
    <li><?= link_to('Экспертные мнения', '@expert_article')?></li>
    <li><?= link_to('События', '@posts?post_type=events') ?></li>
    <li><?= link_to('Опросы', 'questionnaire')?></li>
    <li><?= link_to('Вопрос&Ответ', '@qa')?></li>
  </ul>
</div>

<div class="col search">
  <h3><span></span>Поиск</h3>
  <ul>
    <li>Квартиры <?= link_to('в аренду', Toolkit::getGeoHostByRegionId(Toolkit::getRegionId(), true, false, true) . '/' . Lot::getRoutingType('apartament-rent')) ?><br/>
      и <?= link_to('на продажу', Toolkit::getGeoHostByRegionId(Toolkit::getRegionId(), true, false, true) . '/' . Lot::getRoutingType('apartament-sale')) ?></li>
    <li>Дома <?= link_to('в аренду', Toolkit::getGeoHostByRegionId(Toolkit::getRegionId(), true, false, true) . '/' . Lot::getRoutingType('house-rent')) ?><br/>
      и <?= link_to('на продажу', Toolkit::getGeoHostByRegionId(Toolkit::getRegionId(), true, false, true) . '/' . Lot::getRoutingType('house-sale')) ?></li>
    <li>Коммерческая недвижимость <?= link_to('в аренду', Toolkit::getGeoHostByRegionId(Toolkit::getRegionId(), true, false, true) . '/' . Lot::getRoutingType('commercial-rent')) ?><br/>
      и <?= link_to('на продажу', Toolkit::getGeoHostByRegionId(Toolkit::getRegionId(), true, false, true) . '/' . Lot::getRoutingType('commercial-sale')) ?></li>
    <li>Новостройки <?= link_to('на продажу', Toolkit::getGeoHostByLotType('new_building-sale') . '/' . Lot::getRoutingType('new_building-sale')) ?></li>
    <li>Коттеджные поселки <?= link_to('на продажу', Toolkit::getGeoHostByLotType('cottage-sale') . '/' . Lot::getRoutingType('cottage-sale')) ?></li>
  </ul>
</div>

<div class="col side">
  <h3><span></span>Справка</h3>
  <ul>
    <li><?= link_to('Агентства', '@agencies') ?></li>
    <li><?= link_to('БТИ', '@agencies_bti') ?></li>
  </ul>
</div>

  <div class="col profile">
    <h3><span></span>Личный кабинет</h3>
    <ul>
      <?php if ($sf_user->isAuthenticated()): ?>
      <li class="out last"><?= link_to('Выход', 'user/logout') ?></li>
      <?php else: ?>
      <li><?= link_to('Вход', '/user/login', 'class=popup rel=loginwindow p=login') ?></li>
      <li><?= link_to('Регистрация', '/user/register', 'class=popup rel=reg p=register') ?></li>
      <?php endif ?>
      <li><?php include_partial('global/add-link') ?></li>
    </ul>
  </div>

<?php if (sfConfig::get('lot_noindex')): ?>
  </noindex>
<?php endif ?>
<div class="garin-logo">
  <span>Изобретено в <a target="_blank" href="http://www.garin-studio.ru/">Garin-Studio</a></span>
  <a target="_blank" href="http://www.garin-studio.ru/"><img src="/images/garin-studio.png"></a>
</div>