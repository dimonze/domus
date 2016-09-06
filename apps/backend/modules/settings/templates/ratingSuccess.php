<?php use_helper('I18N', 'Date', 'DomusForm') ?>
<?php include_partial('global/assets') ?>

<div id="sf_admin_container">
  <h1>Рейтинг</h1>

  <?php include_partial('global/flashes') ?>

  <div id="sf_admin_content">

    <div class="notice">
      Текущий статус пересчета: <em><?= $job->status_text ?>.</em>
      <?php if ($job->canRun()): ?>
        <?= link_to('Запустить', 'settings/rating?startjob=1') ?>
      <?php endif ?>
    </div>

    <?= form_tag('settings/rating', 'method=post')?>

      <?php foreach ($data as $context => $rates): ?>
        <fieldset id="sf_fieldset_none">
          <legend><?= $context ?></legend>

          <?php foreach ($rates as $key => $rate): ?>
            <?php if (!is_array($rate)): ?>
                <div class="sf_admin_form_row">
                  <div>
                    <?= label_for("data[$context][$key]", $key) ?>
                    <?= input_tag("data[$context][$key]", $rate) ?>
                  </div>
                </div>

            <?php else: ?>
              <fieldset id="sf_fieldset_none">
                <legend><?= $key ?></legend>
                <?php foreach ($rate as $subkey => $subrate): ?>
                  <?php if (!is_array($subrate)): ?>
                    <div class="sf_admin_form_row">
                      <div>
                        <?= label_for("data[$context][$key][$subkey]", $subkey) ?>
                        <?= input_tag("data[$context][$key][$subkey]", $subrate) ?>
                      </div>
                    </div>
                    <?php else: ?>
                      <fieldset id="sf_fieldset_none">
                        <legend><?= $subkey ?></legend>
                        <?php foreach ($subrate as $sub2key => $sub2rate): ?>
                          <div class="sf_admin_form_row">
                            <div>
                              <?= label_for("data[$context][$key][$subkey][$sub2key]", $sub2key) ?>
                              <?= input_tag("data[$context][$key][$subkey][$sub2key]", $sub2rate) ?>
                            </div>
                          </div>
                        <?php endforeach ?>
                      </fieldset>
                    <?php endif ?>
                <?php endforeach ?>
              </fieldset>
            <?php endif ?>
          <?php endforeach ?>

        </fieldset>
      <?php endforeach ?>

      <ul class="sf_admin_actions form">
        <?= input_tag('submit', 'Сохранить', 'type=submit') ?>
      </ul>
    </form>
  </div>
</div>
