<?php if(sfConfig::get('is_new_building')): ?>
  <?php use_javascript('/form/translations.js', '', array('absolute' => true)) ?>
  <?php use_javascript('/form/search.js?type=new_building-sale', '', array('absolute' => true)) ?>

  <div id="content">
    <div class="form_search">
      <form id="nbmp_form" action="<?php echo Toolkit::getGeoHostByRegionId($sf_user->current_region->id, false) ?>" method="post">
        <div class="form-block">
          <p>Регион</p>
          <select id="sel_region_id" class="select-region" name="region_id">
            <option value="77">Москва</option>
            <option value="50">Московская область</option>
          </select>
        </div>
        <input type="hidden" name="currency" id="currency" value="RUR" />
        <input type="hidden" name="view" id="view" value="" />
        <input type="hidden" name="map-maximized" id="map-maximized" value="0" />
        <input type="hidden" name="restore_advanced" id="restore_advanced" value="1" />
        <input type="hidden" name="location-type" id="location-type" value="form" />
        <div class="form-block">
          <p>Метро / город</p>
          <select id="sel_regionnode" class="select-region" name="regionnode[]">
            <option>Выберите метро</option>
          </select>
        </div>
        <div class="form-block">
          <p>Площадь, м<sup>2</sup></p>
          <?php include_partial('form/configured-select', array('type' => 'new_building-sale', 'id' => 'field72_from', 'attr' => 'class=select-price')) ?>
          <?php include_partial('form/configured-select', array('type' => 'new_building-sale', 'id' => 'field73_to', 'attr' => 'class=select-price')) ?>
        </div>        
        <div class="form-block">
          <p>Цена, руб за м<sup>2</sup></p>
          <select class="select-price" name="price[from]">
            <option>от</option>
          </select>
          <select class="select-price" name="price[to]">
            <option>до</option>
          </select>
        </div>
        <div class="form-block-button">
          <input type="submit" value="Найти">
        </div>
        <input type="hidden" name="sort" id="sort" value="rating-desc" />
        <input type="hidden" name="page" id="page" value="1" />
      </form>
    </div>

    <?php cached_component('news', 'list', array('name' => 'Новости новостроек'), $cache_prefix, rand(1200, 1500)) ?>
    <?php include_partial('banner/block2-inline-spec') ?>
    <?php include_partial('banner/homepage_after_news') ?>
    <?php $lot_list_params = array('type' => 'new_building-sale', 'limit' => 4, 'is_search' => false); ?>
    <?php include_component('lot', 'list', array_merge($lot_list_params, array('name' => 'Новостройки в Москве'))) ?>
    <?php include_component('lot', 'list', array_merge($lot_list_params,
      array('name' => 'Новостройки Подмосковья', 'region_id' => 50, 'route' => '/'.Toolkit::getGeoPseudoTypeForNB(50)))) ?>
    <?php include_partial('banner/block5-down-spec') ?>

    <div class="articles-analytics-box">
      <div class="wrap">
        <?php cached_component('posts', 'postsonhome', array('post_type' => 'article'), $cache_prefix, 1500) ?>
        <?php cached_component('posts', 'postsonhome', array('post_type' => 'analytics'), $cache_prefix, 1200) ?>
      </div>
      <div class="b"></div>
    </div>
    <?php include_partial('banner/homepage_after_postsonhome')?>
    <?php cached_component('expert_article', 'list', null, $cache_prefix, 1900) ?>
    <?php include_partial('banner/homepage_after_expert_articles')?>
    <?php include_partial('banner/homepage_after_rating') ?>
    <?php cached_component('author_article', 'list', null , $cache_prefix, 1700) ?>
    <?php include_partial('banner/homepage_between_authors_and_lots') ?>
    
    <div class="lpages_float_wrap lpages_float_wrap_margined">
      <?php cached_component('landing', 'landingPagesBox', array('type' => 'new_building-sale', 'region_id' => 77),
        $cache_prefix . 'landing_novostroyki_77', rand(50000, 50100)) ?>
    </div>
    <div class="lpages_float_wrap">
      <?php cached_component('landing', 'landingPagesBox', array('type' => 'new_building-sale', 'region_id' => 50),
        $cache_prefix . 'landing_novostroyki_50', rand(50000, 50100)) ?>
    </div>
    <div class="clearBoth"></div>
  </div>
  <div id="aside">
    <?php include_partial('page/aside-head')?>
    <?php include_partial('banner/block3-right-spec') ?>
    <?php include_component('page', 'aside'); ?>
    <div class="rates">
      <div class="wrap">
        <?php cached_component('user', 'companyrating', null, $cache_prefix, rand(1000, 1500)) ?>
      </div>
    </div>
    <div class="rates">
      <div class="wrap">
        <?php cached_component('user', 'realtorrating', null, $cache_prefix, rand(1000, 1500)) ?>
      </div>
    </div>
  </div>
<?php elseif(sfConfig::get('is_cottage')): ?>
  <?php use_javascript('/form/translations.js', '', array('absolute' => true)) ?>
  <?php use_javascript('/form/search.js?type=cottage-sale', '', array('absolute' => true)) ?>
  <div id="content">
    <?php cached_component('news', 'list', array('name' => 'Новости коттеджных поселков'), $cache_prefix, rand(1200, 1500)) ?>
    <?php include_partial('banner/block2-inline-spec') ?>
    <?php include_partial('banner/homepage_after_news') ?>
    
    <?php $lot_list_params = array('type' => 'cottage-sale', 'limit' => 4, 'is_search' => false); ?>
    <?php include_component('lot', 'list', array_merge($lot_list_params, array(
        'name' => 'Коттеджные поселки Подмосковья',
        'params' => array( 'field' => array( 107 => array( 'or' => array('Дом/Коттедж') ) ) ),
        'region_id' => 50, 
        'route' => '/'.Toolkit::getGeoPseudoTypeForCottage(50).'/poselki'
    ))) ?>
    <?php include_component('lot', 'list', array_merge($lot_list_params, array(
        'name' => 'Таунхаусы Подмосковья',
        'params' => array( 'field' => array( 107 => array( 'or' => array('Таунхаусы и Дуплексы') ) ) ),
        'region_id' => 50, 
        'route' => '/'.Toolkit::getGeoPseudoTypeForCottage(50).'/townhouse'
    ))) ?>
    <?php include_component('lot', 'list', array_merge($lot_list_params, array(
        'name' => 'Участки Подмосковья',
        'params' => array( 'field' => array( 107 => array( 'or' => array('Участок') ) ) ),
        'region_id' => 50, 
        'route' => '/'.Toolkit::getGeoPseudoTypeForCottage(50).'/uchastki'
    ))) ?>
    <?php include_component('lot', 'list', array_merge($lot_list_params, array(
        'name' => 'Участки с подрядом в Подмосковье',
        'params' => array( 'field' => array( 107 => array( 'or' => array('Участок с подрядом') ) ) ),
        'region_id' => 50, 
        'route' => '/'.Toolkit::getGeoPseudoTypeForCottage(50).'#r/50/c/RUR/m/0/ra/1/l/form/f107/Uchastok+s+podryadom/s/rating-desc/page/1'
    ))) ?>
    <?php include_partial('banner/block5-down-spec') ?>

    <div class="articles-analytics-box">
      <div class="wrap">
        <?php cached_component('posts', 'postsonhome', array('post_type' => 'article'), $cache_prefix, 1500) ?>
        <?php cached_component('posts', 'postsonhome', array('post_type' => 'analytics'), $cache_prefix, 1200) ?>
      </div>
      <div class="b"></div>
    </div>
    <?php include_partial('banner/homepage_after_postsonhome')?>
    <?php cached_component('expert_article', 'list', null, $cache_prefix, 1900) ?>
    <?php include_partial('banner/homepage_after_expert_articles')?>
    <?php include_partial('banner/homepage_after_rating') ?>
    <?php cached_component('author_article', 'list', null , $cache_prefix, 1700) ?>
    <?php include_partial('banner/homepage_between_authors_and_lots') ?>
  </div>
  <div id="aside">
    <?php include_partial('page/aside-head')?>
    <?php include_partial('banner/block3-right-spec') ?>
    <?php include_component('page', 'aside'); ?>
  </div>
<?php else: ?>
  <div id="content">
    <?php cached_component('news', 'list', null, $cache_prefix, rand(1200, 1500)) ?>
    <?php include_partial('banner/block2-inline-spec') ?>
    <?php include_partial('banner/homepage_after_news') ?>

    <div class="articles-analytics-box">
      <div class="wrap">
        <?php cached_component('posts', 'postsonhome', array('post_type' => 'article'), $cache_prefix, 1500) ?>
        <?php cached_component('posts', 'postsonhome', array('post_type' => 'analytics'), $cache_prefix, 1200) ?>
      </div>
      <div class="b"></div>
    </div>
    <?php include_partial('banner/homepage_after_postsonhome')?>
    <?php cached_component('expert_article', 'list', null, $cache_prefix, 1900) ?>
    <?php include_partial('banner/homepage_after_expert_articles')?>
    
    <?php include_partial('banner/homepage_after_rating') ?>
    <?php cached_component('author_article', 'list', null , $cache_prefix, 1700) ?>
    <?php include_partial('banner/homepage_between_authors_and_lots') ?>
    <?php include_partial('banner/block5-down-spec') ?>
    <?php foreach (sfConfig::get('app_lot_types') as $type => $names): ?>
      <?php if (!Toolkit::isSubdomain($type)): ?>
        <?php include_component('lot', 'list', array('type' => $type, 'limit' => 4, 'is_search' => false)) ?>
      <?php endif ?>
      <?php if ($type == 'apartament-rent'): ?>
        <?php include_partial('banner/homepage_after_list')?>
      <?php endif ?>
      <?php if ($type == 'house-rent'): ?>
        <?php include_partial('banner/homepage_after_house_rent')?>
      <?php endif ?>
    <?php endforeach ?>
  </div>

  <div id="aside">
    <?php include_partial('page/aside-head')?>
    <?php include_partial('banner/block3-right-spec') ?>
    <?php include_component('page', 'aside'); ?>
    <div class="rates">
      <div class="wrap">
        <?php cached_component('user', 'companyrating', null, $cache_prefix, rand(1000, 1500)) ?>
      </div>
    </div>
    <div class="rates">
      <div class="wrap">
        <?php cached_component('user', 'realtorrating', null, $cache_prefix, rand(1000, 1500)) ?>
      </div>
    </div>
  </div>
<?php endif ?>