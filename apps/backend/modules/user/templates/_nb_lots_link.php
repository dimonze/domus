<?php
$counts = $user->getLotsCountsByStatus();
if(!empty($counts)):
  $link = '';
  $all = 0;
  foreach($counts as $status => $count):
    switch($status) {
    case 'active':
      $class = 'on_main';
      $title = 'активные';
      break;
    case 'inactive':
      $class = 'inactive';
      $title = 'не активные';
      break;
    case 'moderate':
      $class = 'moderate';
      $title = 'на модерации';
      break;
    case 'restricted':
      $class = 'restricted';
      $title = 'запрещены';
      break;
    default:
      $class = 'default';
    }
    $all += $count;
    $link .= '/&nbsp;' . link_to(
      $count,
      '/lot/moderate?filter[email]=' . $user->email . '&filter[status]=' . $status,
      array('class' => 'status-'.$class, 'target' => '_blank', 'title' => $title)
    ) . '&nbsp;';
  endforeach;
  $link .= '/&nbsp;' . link_to(
      $all,
      '/lot/moderate?filter[email]=' . $user->email,
      array('class' => 'status-default', 'target' => '_blank', 'title' => 'всего')
    );

  echo 'Объявления:' . substr($link, 1);
endif;