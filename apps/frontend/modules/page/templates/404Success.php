<?php include_partial('banner/error404_downspec_1') ?>
<div class="error e404">
  <h1>404</h1>
  <p>Возможно был неправильно набран адрес,
  либо такой страницы больше на сайте не существует.</p>
  <p>Перейти на <?= link_to('главную', '@homepage') ?>, или в раздел 
  <?= link_to('покупка квартиры', 'search/index?type=apartament-sale') ?>,
  <?= link_to('аренда квартиры',  'search/index?type=apartament-rent') ?>,
  <?= link_to('продажа дома',     'search/index?type=house-sale') ?>,
  <?= link_to('аренда дома',      'search/index?type=house-rent') ?>,
  <?= link_to('продажа офиса',    'search/index?type=commercial-sale') ?>,
  <?= link_to('аренда офиса',     'search/index?type=commercial-rent') ?>.</p>

  <p>Если вы оказались на этой странице перейдя по ссылке
    опубликованной на нашем сайте просим сообщить о этом
  нашему администратору: <?= mail_to(sfConfig::get('app_admin_email'))?></p>
</div>
<?php include_partial('banner/all_spec_zones') ?>