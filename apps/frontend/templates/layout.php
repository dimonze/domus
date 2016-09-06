<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <?php include_http_metas() ?>
  <?php include_metas() ?>
  <?php include_title() ?>
  <?php if (has_slot('canonical-link')) echo get_slot('canonical-link') ?>
  <?php include_stylesheets_qs() ?>
  <?php include_javascripts_qs() ?>

  <script type='text/javascript' src='http://media.mesto.ru/www/delivery/spcjs.php?id=1&block=1&target=_blank'></script>
  <link rel="shortcut icon" href="/images/favicon.ico" />
  <?php if (has_slot('facebook')): ?>
    <?php include_slot('facebook')?>
  <?php endif ?>
  <?php include_component('news', 'marketgid') ?>
</head>
<body>
<div id="settings" data-settings='<?= json_encode(array( 'max_per_page' => sfConfig::get('app_search_max_per_page'))) ?>'></div>
<?php if (has_slot('main_search_params')): ?>
  <?php include_slot('main_search_params')?>
<?php endif ?>
<?php include_component('retargeting', 'code') ?>
<?php if (sfConfig::get('lot_noindex')): ?>
  <noindex>
<?php endif ?>
<?php include_partial('banner/header') ?>
<?php include_partial('menu/header_counters') ?>
 <?php if (sfConfig::get('lot_noindex')): ?>
    </noindex>
  <?php endif ?>
<div id="bg">
<div id="container">
<div id="global-loading"></div>
<div class="global">
  <div class="contentBox">
    <?php if (sfConfig::get('lot_noindex')): ?>
      <noindex>
    <?php endif ?>
    <div id="header" <?= (!sfConfig::get('is_new_building') && !sfConfig::get('is_cottage')) ? 'class="header-main"' : ''?>>
      <div class="logo-title">
        <?= (sfConfig::get('is_cottage')) ? link_to("<div class='cottage-title'></div>", Toolkit::getGeoHostByRegionId(Toolkit::getRegionId(), sfConfig::get('is_cottage'), sfConfig::get('is_cottage'))) : ((sfConfig::get('is_new_building')) ? link_to("<div class='new-building-title'></div>", Toolkit::getGeoHostByRegionId(Toolkit::getRegionId(), sfConfig::get('is_new_building'), sfConfig::get('is_new_building'))) : '') ?>
      </div>
      <?php include_partial('user/menu') ?>
      <?= link_to(image_tag_s('no-repeat', 'alt=Место.ру'), Toolkit::getGeoHostByRegionId(77, true, false), 'class=logo') ?>
      <?php include_component('region', 'menu') ?>
      <?php include_partial('menu/menutopnav')?>
    </div>

    <?php if (sfConfig::get('lot_noindex')): ?>
      </noindex>
    <?php endif ?>

    <div class="wrapper">
      <?php if (sfConfig::get('lot_noindex')): ?>
        <noindex>
      <?php endif ?>

      <?php include_component('menu', 'main') ?>
      <div class="index-adv-teaser">
        <?php if (!sfConfig::get('no_top_spec_banners') && false === strpos(sfContext::getInstance()->getRouting()->getCurrentInternalUri(), 'package-upload=1' )) : ?>
          <div class="wrap">
            <?php include_partial('banner/block1-up-spec') ?>
          </div>
        <?php endif ?>
        <a name="top" class="lotcard"></a>
        <?php include_partial('banner/winner') ?>
      </div>

      <?php if (sfConfig::get('lot_noindex')): ?>
        </noindex>
      <?php endif ?>

      <?php $metas = $sf_response->getMetas(); ?>
      <?php if (isset($metas['name'])): ?>
        <div class="pageHeader">
          <?php if (has_slot('breadcrumbs')): ?>
            <?php include_slot('breadcrumbs')?>
          <?php endif ?>
          <?php if (sfConfig::get('lot_noindex')): ?>
            <noindex>
          <?php endif ?>
          <?php if (has_slot('estate_on_duty')): ?>
            <?php include_slot('estate_on_duty')?>
          <?php endif ?>          
          <?php include_component_slot('compare') ?>
          <?php if (sfConfig::get('lot_noindex')): ?>
            </noindex>
          <?php endif ?>
          
          <div class="social">
            <script type="text/javascript" src="http://yandex.st/share/share.js" charset="utf-8"></script>
            <!--check code-->
            <div class="yashare-auto-init" data-yashareL10n="ru" data-yashareType="button" data-yashareQuickServices="vkontakte,facebook,lj,odnoklassniki,twitter"></div>
          </div>

          <h1>
            <?= html_entity_decode($metas['name'], ENT_QUOTES) ?>
            <?php if ($sf_params->get('module') == 'pm'): ?>
              <?= link_to('Написать', 'pm/add', 'class=popup rel=reg p=create-pm') ?>
            <?php endif ?>
          </h1>
        </div>
      <?php endif ?>

      <?php if (sfConfig::get('lot_noindex')): ?>
        <noindex>
      <?php endif ?>
      <?php include_partial('global/flash') ?>

      <?php if (sfConfig::get('lot_noindex')): ?>
         </noindex>
      <?php endif ?>

      <?php echo $sf_content ?>

      <?php if (sfConfig::get('lot_noindex')): ?>
        <noindex>
      <?php endif ?>
      <?php include_component_slot('sidebar') ?>

       <?php if (sfConfig::get('lot_noindex')): ?>
          </noindex>
       <?php endif ?>
      <div class="clearBoth"></div>
      <?php if (has_slot('banners_after_content')): ?>
        <?php include_slot('banners_after_content')?>
      <?php endif ?>
    </div>
  </div>
</div>
<div class="seo-links"><?= get_slot('seo-links') ?></div>
<div class="wrap">
  <?php if (sfConfig::get('lot_noindex')): ?>
    <noindex>
  <?php endif ?>

  <?php $banner = sfConfig::get('banner')?>
  <?php if ($banner): ?>
    <?php if ($banner == 'usercard'): ?>
      <?php include_partial('banner/usercard_footer') ?>
    <?php elseif ($banner == 'lot'): ?>
      <?php include_partial('banner/lot_footer') ?>
    <?php elseif ($banner == 'search'): ?>
      <?php include_partial('banner/search_footer') ?>
    <?php endif ?>
  <?php endif ?>

  <?php if (sfConfig::get('lot_noindex')): ?>
    </noindex>
  <?php endif ?>
</div>
<div class="district-description"><?= get_slot('district-description') ?></div>
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
