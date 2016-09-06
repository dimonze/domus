<?php $region_id = Toolkit::getRegionId(); $url = ''; ?>
<div class="fix_block">
  <div class="nb-nav" id="nb-nav">
    <div class="nb-nav__block">
      <?php foreach(sfConfig::get('app_mainmenu') as $parts): ?>
        <div class="nb-nav__box nb-nav__box_<?= $parts['class'] ?>">
          <h4 class="nb-nav__header"><?= $parts['name'] ?></h4>
          <ul class="nb-nav__list<?= sfConfig::get('is_cottage') ? ' nb-nav__list_cottage' : '' ?>">
            <?php foreach($parts['fields'] as $field): 
                if(!empty($field['to_cottage']) && !sfConfig::get('is_cottage')) continue;
              ?>
              <li class="nb-nav__item<?= (Lot::getRealType($sf_params->get('type')) == $field['type']) ? ' nb-nav__item_current' : '' ?>">
              <b><?= $field['name'] ?></b>
                <ul class="nb-subnav__list">
                  <?php foreach($field['presets'] as $k => $preset): ?>
                    <?php
                    //from anywhere to 'novostroyki'
                    if(!empty($field['to_nb'])) {
                      $url = 'http://' . sfConfig::get('app_new_building_domain');
                    }
                    //from 'novostroyki' to 'moskva'
                    else if(!empty($field['to_cottage'])) {
                      $url = 'http://' . sfConfig::get('app_cottage_domain');
                    }
                    else {
                      $url = Toolkit::getGeoHostByRegionId($region_id, true);
                    }
                    ?>
                    <li class="nb-subnav__item"><a href="<?= $url . $preset['url'] ?>"<?php if(!empty($field['to_nb'])) echo ' class="nb'.$field['regions'][$k].'"' ?>><?= $preset['name'] ?></a></li>
                  <?php endforeach; ?>
                </ul>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endforeach; ?>
      <div class="clearBoth"></div>
    </div>
    <div class="nb-nav__blue-line"></div>
  </div>
</div>