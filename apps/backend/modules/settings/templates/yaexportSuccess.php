<?php use_helper('I18N', 'Date', 'DomusForm') ?>
<?php include_partial('global/assets') ?>

<div id="sf_admin_container">
  <h1><?php echo __('Настройки экспорта объявлений в Yandex', array(), 'messages') ?></h1>

  <?php include_partial('global/flashes') ?>

  <div id="sf_admin_content">
    <p><a href="http://www.mesto.ru/uploads/8TqPnqgq9Aucm7ZRh7feCQFqH5wnofYZzzhYMhrlKvew9Um6u2.yrl">http://www.mesto.ru/uploads/8TqPnqgq9Aucm7ZRh7feCQFqH5wnofYZzzhYMhrlKvew9Um6u2.yrl</a></p>
    <?= form_tag('settings/yaexport', 'method=post')?>
    <fieldset id="sf_fieldset_none">
      <legend>Пользователи</legend>
      <?php foreach($config['users']['types'] as $user_type => $user): ?>
      <div class="sf_admin_form_row">
        <?= label_for("config[users][types][$user_type][value]", $user['name']) ?>
        <?= input_hidden_tag("config[users][types][$user_type][name]", $user['name']) ?>
        <?= checkbox_tag("config[users][types][$user_type][value]", $user['value'], $user['value']) ?>
      </div>
      <?php endforeach; ?>
    </fieldset>
    <fieldset id="sf_fieldset_none">
      <legend>Источники</legend>
      <?php foreach($config['users']['sources'] as $source_id => $source): ?>
      <div class="sf_admin_form_row">
        <?= label_for("config[users][sources][$source_id][value]", $source['name']) ?>
        <?= input_hidden_tag("config[users][sources][$source_id][name]", $source['name']) ?>
        <?= checkbox_tag("config[users][sources][$source_id][value]", $source['value'], $source['value']) ?>
      </div>
      <?php endforeach; ?>
    </fieldset>
    <fieldset id="sf_fieldset_none">
      <legend>Партнёры</legend>
      <?php if(isset($config['users']['partners'])): foreach($config['users']['partners'] as $group_id => $partner): ?>
      <div class="sf_admin_form_row">
        <?= label_for("config[users][partners][$group_id][value]", 'Выгружать объявления партнёров') ?>
        <?= checkbox_tag("config[users][partners][$group_id][value]", $partner['value'], $partner['value']) ?>
      </div>
      <?php endforeach; endif; ?>
    </fieldset>
    <fieldset id="sf_fieldset_none">
      <legend>Тип предложения</legend>
      <?php foreach($config['lot_type'] as $type => $val): ?>
        <?php if (!in_array($type, array('apartament-sale', 'apartament-rent', 'house-sale', 'house-rent'))): ?>
          <?php continue ?>
        <?php endif ?>
        <div class="sf_admin_form_row">
          <?= label_for("config[lot_type][$type][value]", $val['name']) ?>
          <?= input_hidden_tag("config[lot_type][$type][name]",  $val['name']) ?>
          <?= checkbox_tag("config[lot_type][$type][value]", $val['value'], $val['value']) ?>
        </div>
      <?php endforeach; ?>
    </fieldset>
        <ul class="sf_admin_actions form">
          <?= input_tag('submit', 'Сохранить', 'type=submit') ?>
        </ul>
    <fieldset id="sf_fieldset_none">
      <legend>Регионы</legend>
      <?php foreach($config['regions'] as $region_id => $region): ?>
      <div class="sf_admin_form_row">
        <?= label_for("config[regions][$region_id][value]", $region['name']) ?>
        <?= input_hidden_tag("config[regions][$region_id][name]", $region['name']) ?>
        <?= checkbox_tag("config[regions][$region_id][value]", $region['value'], $region['value']) ?><br/>
        <?= input_tag("config[regions][$region_id][limit]", $region['limit']) ?>
      </div>
      <?php endforeach; ?>
    </fieldset>

        <ul class="sf_admin_actions form">
          <?= input_tag('submit', 'Сохранить', 'type=submit') ?>
        </ul>
    </form>
  </div>
</div>
