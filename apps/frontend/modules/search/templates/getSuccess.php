<?php
switch ($sf_params->get('current_type')){
  case 'apartament-sale': case 'apartament-rent':  case 'house-sale':
    $type = array('%sо %s предложение', '%sо %s предложения', '%sо %s предложений'); break;
  case 'house-rent':
    $type = array('%s %s дом', '%sо %s дома', '%sо %s домов'); break;
  case 'commercial-sale': case 'commercial-rent':
  default:
    $type = array('%s %s объект', '%sо %s объекта', '%sо %s объектов');

}

$is_new_building = in_array($sf_params->get('current_type'), array('new_building-sale', 'new_building-rent'));
$is_nb = sfConfig::get('is_new_building');

foreach ($type as $i => $name) {
  if (($total = $pager->getNbResults()) > 0) {
    $type[$i] = sprintf($name, 'найден', ($total == $pager->getMaxRecordLimit() ? 'более ' : '') . $total);
  }
  else {
    $type[$i] = sprintf($name, 'не найден', '');
  }
}
$res_title =  ending($pager->getNbResults(), $type[0], $type[1], $type[2]);
$sf_response->addMeta('name', $res_title);
?>

<?= input_hidden_tag('res_title', $res_title) ?>

<?php
  /* "Посадочные" страницы */
  if ($sf_user->hasCredential(array(  0 => 'seo-landing' ))) {
    if(!isset($landing)) {
      echo link_to('<span>Создать "посадочную" страницу</span>',
              'search/landing', 'class=newObjectsInfo popup rel=auth') . '<br />';
    } else {
      echo link_to('<span>Редактировать "посадочную" страницу</span>',
              '/backend.php/landing/' . $landing . '/edit', 'class=newObjectsInfo rel=auth') . '<br />';
    }
  }
?>

<?= link_to('<span>Информировать о новых объектах в заданных условиях поиска</span>',
            'search/notify', 'class=newObjectsInfo popup rel=auth') ?>
<?php if ($pager->getNbResults() > 0): ?>
  <a href="#top" onclick="return false;" class="advertCount">
    <?php if($is_new_building): ?><?= include_component('lot', 'additNbInfo', array(
      'region' => $sf_user->current_region,
      'regionnode' => $sf_request->getParameter('regionnode', array()),
      'type' => $sf_request->getParameter('type'),
      'currency' => $sf_request->getParameter('currency'),
      'nb_results' => $pager->getNbResults()
     )); ?>
    <? elseif(sfConfig::get('is_cottage')): #17449
      $prefix = json_decode($meta_data, true);
      if(!empty($prefix['h1'])) {
        $location_pos = min(array_filter(array(
          mb_strpos($prefix['h1'], ' у '), 
          mb_strpos($prefix['h1'], ' в '), 
          mb_strpos($prefix['h1'], ' по ')
        )));
        $prefix = empty($location_pos) ? null : mb_substr($prefix['h1'], $location_pos);
        $prefix = preg_replace('#в Московской области#u','Подмосковья',$prefix);
      }
      echo lot_type_anchor(
              $sf_params->get('current_type'), 
              $pager->getNbResults(), 
              $sf_request->getParameter('field'),
              $prefix
           );
    ?>
    <?php else: ?>
    <?= lot_type_anchor($sf_params->get('current_type'), $pager->getNbResults())?>
    <?php endif ?>
  </a><br />
<?php endif ?>

<?php if (!$pager->getNbResults() && !$is_new_building): ?>
  <br />
  <p>Объявлений с такими параметрами не найдено. Посмотрите предложения в близлежащих населенных пунктах.</p>
  <p>Станьте первым в этом разделе!
Вы можете
      <?php include_partial('global/add-link', array('text' => 'добавить объявление')) ?>
    совершенно бесплатно, потратив на это всего лишь 5 минут Вашего времени.</p>
  <?php if (!$sf_user->isAuthenticated()): ?>
    <p>
        <?php include_partial('global/add-link', array('text' => 'Добавление объявлений')) ?>
      доступно для зарегистрированных пользователей, если вы еще не зарегистрированы,
      то можете подать объявление сразу после заполнения краткой
        <?php include_partial('global/add-link', array('text' => 'регистрационной формы',
                                                       'default' => 'register')) ?>
      .</p>
  <?php endif ?>

  <?php if($is_nb): ?>
    <?php $a = range(1,7); shuffle($a); foreach ($a as $i) {include_partial('banner/spec_item', array('place' => $place + $i));} ?>
    <?php $a = range(1,3); shuffle($a); foreach ($a as $i) {include_partial('banner/spec_item', array('place' => $place + $i + rand(1,3) * 1000));} ?>
  <?php endif; ?>

<?php else: ?>
  <div class="sortBox">
    <div class="floatLeft">
      <select name="sort" class="select_05">
        <option value="rating-desc">рейтингу &darr;</option>
        <option value="rating-asc">рейтингу &uarr;</option>
        <option value="date-desc">дате &darr;</option>
        <option value="date-asc">дате &uarr;</option>
        <option value="address-asc">адресу &uarr;</option>
        <option value="address-desc">адресу &darr;</option>
        <option value="size-asc">площади &uarr;</option>
        <option value="size-desc">площади &darr;</option>
        <option value="price-asc">цене &uarr;</option>
        <option value="price-desc">цене &darr;</option>
        <?php if ($sf_params->get('type') != 'new_building-sale'): ?>
          <option value="seller-asc">продавцу &uarr;</option>
          <option value="seller-desc">продавцу &darr;</option>
        <?php endif ?>
      </select>
    </div>

  <ul class="view-switch">
    <li><a class="action_09 <?php if ($sf_params->get('view') != 'tile') echo 'action_09-active active' ?>" href="#"></a></li>
    <li><a class="action_10 <?php if ($sf_params->get('view') == 'tile') echo 'action_10-active active' ?>" href="#"></a></li>
  </ul>

    <div class="sortRight">
      <?php include_partial('global/pagination-small',
                            array(
                              'pager' => $pager,
                              'url_type' => $sf_params->get('pager_type'),
                            )) ?>

      <ul class="currencyList">
        <li><a href="#" rel="RUR">p.</a></li>
        <li><a href="#" rel="USD">$</a></li>
        <li><a href="#" rel="EUR">&euro;</a></li>
      </ul>
    </div>
    <div class="clearBoth"></div>
  </div>

  <script type="text/javascript">
  <?php if ($sf_params->get('view') == 'tile'): ?>
    $('#result').addClass('block-view');
  <?php else: ?>
    $('#result').removeClass('block-view');
  <?php endif; ?>
  </script>
  <?php if ($is_new_building && !$pager->getNbResults()): ?>
    <?php include_partial('banner/spec_target_item') ?>
  <?php endif ?>
  <?php if($pager->getNbResults()): ?>
    <?php foreach ($pager->getResults() as $id => $lot): ?>
      <?php if ($id != 0 && !($id % 3)): ?>
      <?php endif ?>
        <?php $for_items  = $sf_params->get('view') == 'tile' ? 4 : 3; ?>
        <?php $nb_top_cnt = $sf_params->get('view') == 'tile' ? 3 : 3; ?>
        <?php if(!($id % $for_items) && ($id || $is_nb)): ?>
          <?php $counter = $is_nb && !$id ? $nb_top_cnt : 1; ?>
          <?php $place   = $is_nb && !$id ? 0 : ($is_nb ? $id/$for_items + 2 : $id/$for_items - 1) ?>
          <?php if ($counter == $nb_top_cnt && $is_nb): ?>
            <?php include_partial('banner/spec_target_item') ?>
          <?php else: ?>
            <?php for($i = 1; $i <= $counter; $i++): ?>
              <?php include_partial('banner/spec_item', array('place' => $place + $i, 'count' => $counter, 'iframe' => !$id && $is_new_building)) ?>
            <?php endfor; ?>
          <?php endif ?>
        <?php endif; ?>

        <?php if ($sf_params->get('view') == 'tile'): ?>
          <?php $pos_left = !($id % 2); ?>
          <?php if ($pos_left): ?>
            <div class="items-row-wrap">
            <?php if ($lot->active): ?>
              <?php include_partial('lot/item_block', array('lot' => $lot, 'currency' => $currency, 'pos_class' => 'fl')) ?>
            <?php endif; ?>
          <?php else: ?>
            <?php if ($lot->active): ?>
              <?php include_partial('lot/item_block', array('lot' => $lot, 'currency' => $currency, 'pos_class' => 'fr')) ?>
            <?php endif; ?>
            </div>
          <?php endif; ?>
        <?php else: ?>
        <?php include_partial('lot/item', array('lot' => $lot, 'currency' => $currency, 'add_adv' => true)) ?>
      <?php endif ?>

    <?php endforeach ?>
  <?php endif ?>

<script type="text/javascript">function setHeight(a,b){a.style.height=b+"px"}function getHeight(a){var b=0;if(a.innerHeight){b=a.innerHeight}else if(a.clientHeight){b=a.clientHeight}return b}var result_box=document.getElementById("result"),divs=result_box.childNodes,rows=[];for(var i=divs.length-1;i--;i>=0){if(divs[i].className==="items-row-wrap"){var cols=function(){var a=[];for(var b=divs[i].childNodes.length-1;b--;b>=0){if(divs[i].childNodes[b].tagName==="DIV"){a.push(divs[i].childNodes[b])}}return a}(),max_height=Math.max(getHeight(cols[0]),getHeight(cols[1]));setHeight(cols[0],max_height);setHeight(cols[1],max_height)}}</script>

  <div class="clearBoth"></div>
  <?php
    include_partial('global/pagination',
      array(
        'pager' => $pager,
        'url_type' => $sf_params->get('pager_type'),
      )
    )
  ?>

<?php endif ?>
<?php if (!empty($links)): ?>
  <div class="seo_links">
    <?= $links ?>
  </div>
<?php endif ?>

<?php if (!empty($district_description)): ?>
  <div class="district_description">
    <?= $district_description ?>
  </div>
<?php endif ?>

<span class="meta-data" data='<?= $meta_data ?>' style="display:none"></span>
