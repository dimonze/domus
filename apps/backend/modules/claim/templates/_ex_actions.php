<?php
switch ($claim->status) {
  case 'new':
    echo link_to('Рассмотреть', 'claim/?action=confirm&id='.$claim->id);
    break;
  case 'confirmed':
    break;
  case 'not_confirmed':
    break;
  case 'on_control':
    break;
  case 'need_check':
    echo link_to('Поставить на контроль', 'claim/?action=to_control&id='.$claim->id);
    echo link_to('Объявление исправлено', 'claim/?action=fixed&id='.$claim->id);
    break;
  case 'fixed':
    echo 'Жалобы была расмотрена';
    break;
}
