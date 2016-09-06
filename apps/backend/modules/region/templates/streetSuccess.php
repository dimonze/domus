<?php use_helper('DomusForm') ?>

<form action="<?= url_for('region/updatestreet') ?>" method="post" class="ajax-validate">
  <?= input_hidden_tag('id', $regionnode->id) ?>
  <?= input_hidden_tag('letter', $sf_params->get('letter')) ?>

  <fieldset style="margin: 0">
    <legend>
      <span class="prependClose">Список улиц <?= $regionnode->full_name ?></span>
    </legend>

    <div class="street-letters">
      <?php foreach ($letters as $letter): ?>
        <?= link_to(
          $letter['name'],
          'region/street?id='.$regionnode->id.'&letter='.$letter['name'],
          'rel=reg class=popup ' . ($letter['name'] == $sf_params->get('letter') ? 'current' : '' )) ?> &nbsp;
      <?php endforeach ?>
    </div>

    <?php if (count($streets)): ?>
      <ul id="street-list">
        <?php foreach ($streets as $street): ?>
          <li rel="<?= $street->name ?>">
            <?= input_tag('street[delete][]', $street->name, 'type=checkbox') ?>
            <span real_name="<?= $street->name . '|' . $street->socr ?>">
              <?= $street->full_name ?>
            </span>
          </li>
        <?php endforeach ?>
      </ul>

    <?php endif ?>

    <div class="street-tools">
      <?php if (count($streets)): ?>
        <?= submit_tag('Переименовать и удалить выбранные') ?>
        <small>
          выбрать <a href="#" class="select-all">все</a>,
          <a href="#" class="select-none">ни одной</a>
        </small><br />
      <?php endif ?>
        
      <?= link_to(
        'Загрузить список',
        'region/importstreet?id='.$regionnode->id,
        'class=popup rel=reg') ?>
    </div>

  </fieldset>
</form>