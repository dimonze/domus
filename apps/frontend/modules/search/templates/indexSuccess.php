<?php use_javascript('/currency.js', '', array('absolute' => true)) ?>
<?php use_javascript('/form/translations.js?' . time(), '', array('absolute' => true)) ?>
<?php use_javascript('/form/gmap.js?' . time(), '', array('absolute' => true)) ?>
<?php use_javascript('/form/search.js?type=' . Lot::getRealType($sf_params->get('type')), '', array('absolute' => true)) ?>
<?php use_javascript('search.js') ?>
<?php use_javascript('_regions.js') ?>
<?php use_javascript('raphael.min.js') ?>

<?php if(!empty($main_search_params)): ?>
  <?php slot('main_search_params'); ?>
    <div id="main-search-params" data-params='<?= $main_search_params ?>'></div>
  <?php end_slot(); ?>
<?php endif;?>


<div class="contentLeft_02">
  <div class="searchObjectBox">
    <?php if (in_array(Lot::getRealType($sf_params->get('type')), array('new_building-sale', 'apartament-sale'))): ?>
    <div class="house-tabs cf <?= (Lot::getRealType($sf_params->get('type')) == 'new_building-sale') ? 'new-building' : '' ?>">
      <ul>
        <?php if (Lot::getRealType($sf_params->get('type')) == 'new_building-sale'): ?>
          <li class="current left"><span>
            <?php if($sf_user->current_region->id == 50): ?>
              Новостройки Подмосковья
            <?php elseif ($sf_user->current_region->id == 77): ?>
              Новостройки Москвы
            <?php endif ?>
          </span><i></i></li>
        <?php else: ?>
          <li class="first current"><span>Квартиры</span><i></i></li>
        <?php $region_id = Toolkit::getRegionId(); ?>
          <li><a onclick="<?php if(!in_array($region_id, array(77,50,78,47))): ?>show_popup('<div>Раздел находится на стадии наполнения</div>','loginwindow', 'Раздел закрыт'); return false;<?php endif ?>"
               href="<?= (in_array($region_id, array(77,50,78,47))) ? 'http://' . sfConfig::get('app_new_building_domain') . '/' .  Toolkit::getGeoPseudoTypeForNB($region_id) : '#' ?>">Новостройки</a><i></i></li>
        <?php endif; ?>
      </ul>
    </div>
    <?php else: ?>
    <div class="boxBack_03"></div>
    <?php endif;?>
    <div class="boxBack_04">
      <form action="<?= url_for('search/get') ?>" method="post" class="searchObjectForm">

        <?= input_hidden_tag('q', $sf_params->get('q')) ?>
        <?= input_hidden_tag('region_id', $sf_user->current_region->id) ?>
        <?= input_hidden_tag('currency', 'RUR') ?>
        <?= input_hidden_tag('currency_type', '') ?>
        <?= input_hidden_tag('latitude[from]') ?>
        <?= input_hidden_tag('latitude[to]') ?>
        <?= input_hidden_tag('longitude[from]') ?>
        <?= input_hidden_tag('longitude[to]') ?>
        <?= input_hidden_tag('zoom') ?>
        <?= input_hidden_tag('map-maximized', 0) ?>
        <?= input_hidden_tag('restrict_region', 0) ?>
        <?= input_hidden_tag('restore_custom') ?>
        <?= input_hidden_tag('restore_advanced') ?>
        <?= input_hidden_tag('view', $sf_params->get('view')) ?>
        <?php if($sf_params->has('landing')): ?>
        <?= input_hidden_tag('landing', $sf_params->get('landing')) ?>
        <?= input_hidden_tag('landing_lot_title_prefix', $sf_params->get('landing_lot_title_prefix')) ?>
        <?php endif; ?>

        <?php if ($sf_params->has('type')): ?>
          <?= input_hidden_tag('type', Lot::getRealType($sf_params->get('type'))) ?>

          <fieldset>
            <h3>Местоположение</h3>

            <div class="radioBox_01">
              <label>
                <input type="radio" name="location-type" value="map" />
                искать по карте
              </label><br />

              <label>
                <input type="radio" name="location-type" value="form" checked="checked" />
                выбрать из списка
              </label>
            </div>

            <div class="metroBox">
              <?php if($sf_user->current_region->id == 77 || $sf_user->current_region->id == 78): ?>
              <?php
                $metroBox = 'список метро';
                $metroBox_2 = 'список районов';
              ?>
              <?php else: ?>
              <?php
                $metroBox = sfConfig::get('is_cottage') ? 'список районов' : 'список район/город';
                $metroBox_2 = ($sf_user->current_region->id == 50) ? 'список шоссе' : null;
                ?>
              <?php endif ?>
              <span class="link2 select-regionnode" style="display: none;"><?= $metroBox ?></span>
              <a href="#" class="link2 select-regionnode"><?= $metroBox ?></a><br>
              <?php if(!empty($metroBox_2)): ?><a href="#" class="link2 roadnode select-regionnode"><?= $metroBox_2 ?></a><?php endif; ?>
            </div>

            <?php if (strpos(Lot::getRealType($sf_params->get('type')), 'commercial') === 0): ?>
            <div class="commercialtypeBox">
              <h3>Тип недвижимости</h3>
              <a href="#" class="link2 select-commercialtype">выбрать</a>
            </div>
            <?php endif ?>

            <?php include_partial(Lot::getRealType($sf_params->get('type'))) ?>
          </fieldset>

        <?php else: ?>
          <fieldset>
            <h3>Искать в разделе</h3>
            <div class="radioBox_01">
              <?php foreach (sfConfig::get('app_lot_types', array()) as $type => $names): ?>
                <label>
                  <?= input_tag('type', $type, array(
                      'type' => 'radio',
                      'id'   => 'type_'.$type,
                      'href' => url_for('@search?type='.$type.'&region_id='.$sf_user->current_region->id)
                    )) ?>
                  <?= $names['short'] ?>
                </label>
                <br />
              <?php endforeach ?>
            </div>
          </fieldset>

        <?php endif ?>

        <?= input_hidden_tag('sort', 'rating-desc') ?>
        <?= input_hidden_tag('page', 1) ?>
      </form>
    </div>
    <div class="boxBack_05"></div>

    <?php if (Lot::getRealType($sf_params->get('type')) != 'new_building-sale'): ?>     
      <?php include_partial('banner/search-left') ?>
    <?php else: ?>
    <?php endif ?>
    <?php include_partial('banner/block4-left-spec') ?>
    <?php slot('banners_after_content') ?>
      <br/>
      <?php include_partial('banner/block5-down-spec') ?>
    <?php end_slot() ?>
  </div>


  <div class="viewObjects">
    <div id="result">
      <?php if (!empty($results)): ?>
        <?= $results ?>
      <?php endif ?>
    </div>
  </div>
</div>

<div class="contentRight_02 search">

  <?php include_partial('banner/search-right') ?>

  <div class="searchAddr expandable" id="search_addr" style="width: 298px;margin-left: 0px;">
    <i></i>
    <div class="searchAddr-wrap">

      <form action="" id="searchAddrForm">
        <table>
          <colgroup><col width="135"/><col/><col width="70"/></colgroup>
          <tr>
            <td><label for="addr-search">Поиск по адресу:</label></td>
            <th><input type="text" name="" id="addr-search"/></th>
            <td><input type="submit" value="Найти"/></td>
          </tr>
        </table>
      </form>

    </div>
  </div>

  <div class="searchMap" id="search_map">
    <div class="prompt" style="display: none;">
      <p><b>Как искать по карте?</b></p>
      <p>Курсором мыши переместите карту в интересующее Вас место. Масштаб карты
        изменяйте, вращая колесико мыши. На карте отображается по 10 объектов
        недвижимости. Для того, чтобы вывести на карту следующие объекты, нажмите
        вверху карты кнопку "Следующие 10" . Если Вы хотите, чтобы на карте
        отображались объявления из других городов, снимите галочку с ячейки
        "Только в рамках региона".
      </p>
      <form>
        <div>
          <input type="checkbox" id="donotshowanymore">
          <label for="donotshowanymore">Больше не показывать подсказку</label>
          <a class="close">Закрыть подсказку</a>
        </div>
      </form>
    </div>
    <ul class="page-nav" id="pager-map">
      <li rel="prev">&larr;&nbsp;предыдущие 10</li>
      <li rel="next">следующие 10&nbsp;&rarr;</li>
    </ul>
    <div id="search-gmap"></div>
    <div class="boxBack_01">
      <a href="#" class="searchOn">Включить поиск по карте</a>
      <a href="#" class="maximize">Развернуть</a>
      <span class="restrict-region">
        <?= checkbox_tag('ck-restrict-region', 1, true) ?>
        <?= label_for('ck-restrict-region', 'Только в рамках региона') ?>
      </span>
      <div class="clearBoth"></div>
    </div>
  </div>
  <div class="mapPopup" style="display: none;"></div>

  <?php include_partial('banner/block3-right-spec') ?>
  <?php slot('banners_after_content') ?>
    <br/>
    <?php include_partial('banner/block5-down-spec') ?>
  <?php end_slot() ?>
</div>
