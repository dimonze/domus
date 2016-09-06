<legend>
  <a href="#" rel="rayoncol" class="<?= sfConfig::get('is_cottage') ? '' : 'map-switch-width'?> map-switch<?= (empty($stage) || $stage == 'map-popup-metro' ? ' selected' : '') ?>">
    <?= sfConfig::get('is_cottage') ? 'Район' : 'Район / Населенный пункт' ?>
  </a>
  <?php if (!empty($shosse)): ?><a href="#" rel="shossecol" class="map-switch<?= ($stage == 'map-popup-region' ? ' selected' : '') ?>">Шоссе</a><?php endif; ?>
  <div style="clear:both;"></div>
</legend>

<div class="popupCenter" binded="true">
  <div class="body">
    <fieldset>
      
      <div>
        <table style="width: 100%;">
          <tbody>
            <tr>
              <td class="rayoncol" style="display:<?= (empty($stage) || $stage == 'map-popup-metro' ? ' table-cell' : 'none') ?>">
                <ul class="regionnode-list" id="metro-list">
                  <?php foreach ((sfConfig::get('is_cottage') ? $rayon : $nodes) as $node): ?>
                    <?php $node_text = Regionnode::formatName($node->name, $node->socr)?>
                    <li>
                      <label>
                        <input type="checkbox" value="<?= $node_text ?>" /><?= $node_text ?>
                      </label>
                    </li>
                  <?php endforeach ?>
                </ul>
              </td>
              <?php if (!empty($shosse)): ?>
                <td class="shossecol" style="display:<?= ($stage == 'map-popup-region' ? ' table-cell' : 'none') ?>">
                  <ul class="regionnode-list" id="metro-list">
                    <?php foreach ($shosse as $node): ?>
                      <?php $node_text = Regionnode::formatName($node->name, $node->socr)?>
                      <li>
                        <label>
                          <input type="checkbox" value="<?= $node_text ?>" /><?= $node_text ?>
                        </label>
                      </li>
                    <?php endforeach ?>
                  </ul>
                </td>
              <?php endif ?>
            </tr>
          </tbody>
        </table>
        <table class="popup-value">
          <tfoot>
            <tr>
              <td collspan="1">
                <a href="#" class="popupClose">отменить</a>
              </td>
              <td collspan="1">
                <span class="formButton"><input type="submit" value="Применить" /></span>
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </fieldset>
  </div>
</div>