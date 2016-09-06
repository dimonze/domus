<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <?php include_http_metas() ?>
  <?php include_metas() ?>
  <?php include_title() ?>
  <?php include_stylesheets_qs() ?>
  <?php include_javascripts_qs() ?>

  <link rel="shortcut icon" href="/images/favicon.ico" />
  <?php if (has_slot('facebook')): ?>
    <?php include_slot('facebook')?>
  <?php endif ?>
</head>
<body id="front">
<?php include_component('retargeting', 'code') ?>
<?php include_partial('banner/header') ?>
<?php include_partial('menu/header_counters') ?>
<div id="bg">
<div id="container">
<div id="global-loading"></div>
  <div id="header" <?= (!sfConfig::get('is_new_building') && !sfConfig::get('is_cottage')) ? 'class="header-main"' : ''?>>
    <div class="logo-title">
      <?= (sfConfig::get('is_cottage')) ? link_to("<div class='cottage-title'></div>", Toolkit::getGeoHostByRegionId(Toolkit::getRegionId(), sfConfig::get('is_cottage'), sfConfig::get('is_cottage'))) : ((sfConfig::get('is_new_building')) ? link_to("<div class='new-building-title'></div>", Toolkit::getGeoHostByRegionId(Toolkit::getRegionId(), sfConfig::get('is_new_building'), sfConfig::get('is_new_building'))) : '') ?>
    </div>
    <?php include_partial('user/menu') ?>
    <?= link_to(image_tag_s('no-repeat', 'alt=Место.ру'), Toolkit::getGeoHostByRegionId(77, true, false), 'class=logo') ?>
    <?php include_component('region', 'menu') ?>
    <?php include_partial('menu/menutopnav')?>
  </div>

  <div id="wrapper" <?= (sfConfig::get('homepage')) ? 'class="index-page"' : ''?>>
    <?php include_component('menu', 'main') ?>
    <?php #if (sfConfig::get('homepage')): ?>
      <?php #include_partial('menu/menu-tab') ?>
    <?php #endif ?>
    <div class="index-adv-teaser" <?= (sfConfig::get('homepage')) ? 'style="margin-top: -3px;"' : ''?>>
      <?php if (sfConfig::get('homepage')): ?>
        <?php include_partial('banner/winner') ?>
      <?php endif ?>
      <?php if (sfConfig::get('homepage')): ?>
        <?php if (!sfConfig::get('all_banners')): ?>
          <div class="wrap">
          <?php include_partial('banner/block1-up-spec') ?>
          </div>
        <?php endif ?>
      <?php else: ?>
        <div class="wrap">
          <?php include_partial('banner/block1-up-spec') ?>
        </div>
      <?php endif ?>
      <?php if (!sfConfig::get('homepage')): ?>
        <?php include_partial('banner/winner') ?>
      <?php endif ?>
    </div>
    <?php $metas = $sf_response->getMetas(); ?>
    <?php if (!empty($metas['name'])): ?>
      <h1>
        <?= html_entity_decode( (empty($metas['h1']) ? $metas['name'] : $metas['h1']), ENT_QUOTES) ?>
      </h1>
    <?php endif ?>
    <?= $sf_content ?>
  </div>

<div class="wrap">
  <?php include_partial('banner/homepage_footer') ?>
</div>
<?php if (sfConfig::get('homepage') && !sfConfig::get('all_banners') && in_array($sf_user->current_region->id, array(16,23,24,2,38,43,47,50,52,54,55,59,61,63,64,66,72,74,77,78))): ?>
    <div class="district-description">
      <div class="district-description-text">
        <div class="dd-text">
          <?php include_component('region', 'homepage', array('region_id' => $sf_user->current_region->id)) ?>
        </div>
      </div>
    </div>
<?php endif ?>
<div id="footer">
  <div class="lining">
    <?php include_component('menu', 'footer') ?>
  </div>
  <div class="b"></div>
</div>
</div>
</div>
<?php include_partial('banner/lazy_openx') ?>
<?php cached_component('banner', 'invisible', null, 'banner_openx_invisible', 500) ?>
</body>
</html>
