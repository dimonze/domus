<?php use_helper('I18N', 'Date', 'DomusForm') ?>
<?php include_partial('global/assets') ?>

<div id="sf_admin_container">
  <?php if (isset($settings)): ?>
    <h1>Справочники полей форм поиска - <?php echo $type_names['plural'] ?></h1>
  <?php else: ?>
    <h1>Справочники полей форм поиска:</h1>
    <ul>
    <?php foreach ($types as $type => $names): ?>
      <li><?php echo link_to($names['name'], 'settings/search-reference?type='.$type) ?></li>
    <?php endforeach ?>
    </ul>
  <?php endif ?>

  <?php include_partial('global/flashes') ?>

  <div id="sf_admin_content">
    <?php if (isset($settings)): ?>

      <?= form_tag('settings/search-reference', 'method=post')?>
        <?= input_hidden_tag('type', $sf_params->get('type'))?>

        <p>Вводите значения по одному в строку.</p>

        <?php $i = 0 ?>
        <?php $type = $sf_params->get('type') ?>
        <?php foreach ($settings as $id => $field): ?>          
          <?php if (empty($field['label'])): ?>            
          <div style="margin-bottom: 20px; clear:both;">
            <p><?= Lot::$currency_types[$type][$id] ?></p>
            <?php foreach ($field as $currency_type => $row): ?>                                                        
              <fieldset id="sf_fieldset_none" style="width: auto; margin-right: 10px; float: left;">
                <div class="sf_admin_form_row">
                  <div>
                    <?= label_for("reference[$id][$currency_type]", $row['label']) ?>
                    <?= textarea_tag("reference[$id][$currency_type]", isset($row['value']) ?implode("\n", $row['value']) : '') ?>
                  </div>
                </div>
              </fieldset>
            <?php endforeach ?>
          </div>
          <?php else: ?>
            <fieldset id="sf_fieldset_none" style="width: auto; margin-right: 10px; float: left;">
              <div class="sf_admin_form_row">
                <div>
                  <?= label_for("reference[$id]", $field['label']) ?>
                  <?= textarea_tag("reference[$id]", isset($field['value']) ?implode("\n", $field['value']) : '') ?>
                </div>
              </div>
            </fieldset>
            <?= $i++ % 2 ? '<div style="clear: both"></div>' : ''?>
          <?php endif ?>
        <?php endforeach ?>

        <ul class="sf_admin_actions form">
          <li class="sf_admin_action_list"><?php echo link_to('Отмена', 'settings/search-reference') ?></li>
          <?= input_tag('submit', 'Сохранить', 'type=submit') ?>
        </ul>

      </form>

    <?php endif ?>

  </div>
</div>