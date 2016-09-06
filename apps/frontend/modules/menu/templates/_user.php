<div class="contentRight_02">
  <div class="cabinetMenuBox ">
    <div class="boxBack_02">
      <h2>Мой кабинет</h2>
    </div>
    <?php include_component('user', 'submenu', array(
      'class' => 'cabinetMenu padding_11',
      'expanded' => true)) ?>
  </div>

  <?php if (sfConfig::get('show_rating_sidebar', false)): ?>
    <div class="rate-diagFixed">
      <div id="rate-diag" class="rate-diag">
        <h5>
          Заполнено полей:
          <strong rel="filled"></strong>
          из <span rel="total"></span></h5>
        <div class="diag"><span rel="bar"></span></div>
        <p class="rate">Рейтинг: <strong rel="rating"></strong></p>
        <!--
          <a class="show" href="#">О рейтинге</a>
          <p class="hidden">О рейтинге можно сказать многое</p>
        -->
        <div class="b"></div>
      </div>
    </div>
  <?php endif ?>

  <?php include_partial('banner/usermenu') ?>

  <div id="front">
    <div id="wrapper">
      <?php if (sfContext::getInstance()->getActionName() != 'moderate'): ?>
      <?php cached_component('qa', 'list', array('post_type' => 'qa'), $cache_prefix, 1200) ?>
      <?php endif ?>
    </div>
  </div>

</div>
