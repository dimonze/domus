<li class="inner current">
  <a href="#"><em><span><?= ($sf_user->current_region->id == 77) ? 'Новостройки Москвы' : 'Новостройки Подмосковья'?><var></var></span></em></a>
  <div class="dropbox">
    <div class="dropbox-wrap">
      <ul>
        <li>
          <?php if ($sf_user->current_region->id != 77): ?>
            <?= link_to('Новостройки Москвы', Toolkit::getGeoHostByRegionId(77, false)) ?>
          <?php else: ?> 
            <?= link_to('Новостройки Подмосковья', Toolkit::getGeoHostByRegionId(50, false)) ?>
          <?php endif ?>
        </li>
      </ul>
    </div>
  </div>
</li>