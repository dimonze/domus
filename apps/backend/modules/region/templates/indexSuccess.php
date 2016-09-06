<?php use_stylesheet('tree_component') ?>
<?php use_javascript('global.js') ?>
<?php use_javascript('css.js') ?>
<?php use_javascript('jquery.tree_component.js') ?>
<?php use_javascript('backend.region.js') ?>
<?php include_partial('global/assets') ?>

<div id="sf_admin_container">
  <h1>География</h1>
  <?php include_partial('global/flashes') ?>
  <div id="sf_admin_content">
    <p>
      Внимание. Формат редактирования объектов (но не регионов) следующий -
      <span class="format_rule">
        <code>&laquo;название<b>|</b>сокращение&raquo;</code>, например,
        <code>&laquo;Москва|г&raquo;</code> или
        <code>&laquo;Дмитровский|р&raquo;</code>
      </span>
    </p>
    <p>
      <small>Удерживайте ctrl для выбора нескольких объектов</small>
    </p>
  </div>
</div>

<div id="tree"></div>
