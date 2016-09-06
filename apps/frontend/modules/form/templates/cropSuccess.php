<?php use_javascript('crop.js') ?>
<?php use_javascript('jquery.Jcrop.js') ?>
<?php use_stylesheet('jquery.Jcrop.css') ?>
<?php include_javascripts() ?>
<?php include_stylesheets() ?>
<style type="text/css">
  h1 {font-size: 22px; margin: 12px 0 20px;}
  body {background: rgb(196,230,240);}
</style>

<div style="margin-left: 100px;">
  <h1>Предварительная обработка изображений</h1>
  <?php if($error): ?>
  <ul class="errors">
    <li><?= $error ?></li>
  </ul>
  <?php endif; ?>
  <form action="/form/crop<?= $type ? '/type/' . $type : null ?>" method="post" enctype="multipart/form-data" style="padding: 0 0 20px;">
  <?= $form['image']->renderLabel() ?>
  <?php if(isset($form)): ?>
    <?= $form['image'] ?>
    <?= submit_tag('Загрузить ' . $image_name, array('name' => 'load_image')) ?>
  <?php endif ?>
  <?php if ($message): ?>
    <h1><?php echo $message ?></h1>
  <?php else: ?>
  <div style="margin: 0 0 10px;">
    <?php if ($file): ?>
    <?= input_tag('save_crop', 'Сохранить ' . $image_name, array('type' => 'button', 'disabled' => true))?>
    Пожалуйста, выделите область на фото/лого, которая будет загружена
      <?= image_tag($file, array('name' => 'imagecropbox')) ?>
      <?= input_tag('crop_image', '', array('type' => 'hidden')) ?>
      <?= input_tag('coord_x', $file, array('type' => 'hidden')) ?>
      <?= input_tag('coord_y', $file, array('type' => 'hidden')) ?>
      <?= input_tag('width', $file, array('type' => 'hidden')) ?>
      <?= input_tag('height', $file, array('type' => 'hidden')) ?>
      <?= input_tag('image_file', $file, array('type' => 'hidden')) ?>
    <?php endif ?>
  </div>
  <?= input_tag('save_crop', 'Сохранить ' . $image_name, array('type' => 'button', 'disabled' => true))?>
  <?php endif ?>
  </form>
</div>
