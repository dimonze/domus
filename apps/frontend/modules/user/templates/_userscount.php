<?php $type = array('собственник', 'собственника', 'собственников') ?>
<?php $owners = ending($count, $type[0], $type[1], $type[2]) ?>
<span id="users"><?= preg_replace('/(\d+)(\d{3})$/', '\\1&nbsp;\\2', $count) . ' ' . $owners ?></span>