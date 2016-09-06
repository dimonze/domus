<?php use_javascript('pm.js'); ?>
<?php
$message_class = array(
  'low'  => 'exclamation-green',
  'mid'  => 'exclamation-yellow',
  'high' => 'exclamation-red',
);
?>
<?php foreach($messages as $message): ?>
<div class="profile-message <?= $message_class[$message->priority] ?>">
  <?= link_to ('x', 'pm/read-and-next?id='.$message->id, 'class=inner') ?>
  <h5><?= date('H:i d.m.Y' ,strtotime($message->sent_at)) ?></h5>
  <p><?= $message->subject ?></p>
  <p><?= mb_substr($message->message,0,500,'utf-8') ?></p>
  <?= link_to ('Подробнее', 'user/pm#m'.$message->id, 'class=more') ?>
</div>
<?php endforeach; ?>
 