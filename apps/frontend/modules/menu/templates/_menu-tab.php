<div class="menu-tabs">
  <?php $types = sfConfig::get('app_lot_types')?>
  <?php foreach (array_keys($types) as $id => $type): ?>
    <div class="tab" <?= ($id == 0) ? 'style="display:block;"' : 'style="display:none;"'?>>
      <?= link_to_if($hasLots = $sf_user->current_region->hasLots($type),
             'Поиск недвижимости на карте',
             '@search?type=' . $type,
             array('class' => 'tab-link-search')
           ) ?>
      <span class="tab-link-stat"><?php include_component('user', 'userscount') ?></span>
      <div class="adv-counter">
        <?php cached_component(
          'lot',
          'lotscount',
          array('type' => $type),
          sprintf('lots_count_%s_%s', $type, $sf_user->current_region->id),
          rand(2000, 2500))
        ?>
      </div>
      <?= link_to_if($hasLots,'<span>' . $types[$type]['menu-search'] .'</span>', '@search?type=' . $type, array('class' => 'button link-local')) ?>
      <?= include_partial('global/add-link', array(
        'text' => '<span>Добавить объявление</span>',
        'class' => 'button link-new-ad'))
      ?>
    </div>
  <?php endforeach ?>
</div>