<?php if (empty($text)) $text = 'Разместить объявление' ?>
<?php $url = 'user/login' ?>
<?php $rel = 'loginwindow' ?>
<?php if (!empty($default)): ?>
  <?php $url = 'user/' . $default ?>
  <?php $rel = 'reg' ?>
<?php endif ?>

<?php if (empty($class)): ?>
  <?php $class = '' ?>
<?php endif ?>
<?php if ($sf_user->isAuthenticated()): ?>
  <?php echo link_to($text, 'lot/add', 'class=' . $class) ?>
<?php else: ?>
  <?php echo link_to($text, $url . '?forward=' . url_for('lot/add'), 'class=' . $class .' popup rel=' . $rel) ?>
<?php endif ?>