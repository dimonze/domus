<?php use_helper('I18N', 'Date', 'DomusForm') ?>
<?php include_partial('global/assets') ?>

<div id="sf_admin_container">
  <h1><?php echo __('Мета-данные', array(), 'messages') ?></h1>

  <?php include_partial('global/flashes') ?>

  <div id="sf_admin_content">
    <?= form_tag('settings/meta', 'method=post')?>

      <?php foreach ($data as $module => $actions): ?>
        <fieldset id="sf_fieldset_none">
          <legend><?= $module ?></legend>

          <?php foreach ($actions as $action => $rows): ?>
            <fieldset id="sf_fieldset_none">
              <legend><?= $action ?></legend>

              <?php foreach ($rows as $name => $row): ?>
              <?php if(is_array($row)):?>
              <fieldset id="sf_fieldset_none">
                <legend><?=$name?></legend>
                <?php foreach ($row as $param_name => $param): ?>
                
                  <div class="sf_admin_form_row">
                    <div>
                      <?= label_for("data[$module][$action][$name][$param_name]", $param_name) ?>
                      <?php if ($name == 'title'): ?>
                        <?= input_tag("data[$module][$action][$name][$param_name]", $param) ?>
                      <?php else: ?>
                        <?= textarea_tag("data[$module][$action][$name][$param_name]", $param) ?>
                      <?php endif ?>
                    </div>
                  </div>
                <?php endforeach ?>
                </fieldset>
              <?php else: ?>
                <div class="sf_admin_form_row">
                  <div>
                    <?= label_for("data[$module][$action][$name]", $name) ?>
                    <?php if ($name == 'title'): ?>
                      <?= input_tag("data[$module][$action][$name]", $row) ?>
                    <?php else: ?>
                      <?= textarea_tag("data[$module][$action][$name]", $row) ?>
                    <?php endif ?>
                  </div>
                </div>
              <?php endif ?>
              <?php endforeach ?>

              <div class="sf_admin_form_row"><div>
                <small>
                Доступные шаблоны подстановки:
                <?= implode(' ', MetaParse::getPlaceholders($module)) ?>
                *
                </small>
              </div></div>

            </fieldset>
          <?php endforeach ?>

        </fieldset>
      <?php endforeach ?>

      <p>* Доступны в зависимости от контекста</p>

      <ul class="sf_admin_actions form">
        <?= input_tag('submit', 'Сохранить', 'type=submit') ?>
      </ul>

    </form>
  </div>
</div>
