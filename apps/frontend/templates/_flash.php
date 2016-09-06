<?php foreach ($sf_user->getAttributeHolder()->getNames('symfony/user/sfUser/flash') as $name): ?>
  <?php $class = strpos($name, 'error') ? 'error' : 'success' ?>
  <div class="flash <?= $class ?>">
    <?= $sf_user->getFlash($name) ?>
  </div>
  <?php $sf_user->getAttributeHolder()->set($name, true, 'symfony/user/sfUser/flash/remove') ?>
<?php endforeach ?>
