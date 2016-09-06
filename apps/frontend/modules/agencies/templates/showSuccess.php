<?php use_helper('Text') ?>
<?php if ('bti' == $agency->type): ?>
  <?= $sf_response->addMeta('name', $agency->name) ?>
<?php else: ?>
  <?= $sf_response->addMeta('name', sprintf("Агентство недвижимости %s в %s", $agency->name, $agency->Region)) ?>
<?php endif ?>
<?= $sf_response->addMeta('title', $title) ?>
<?= $sf_response->addMeta('description', $description) ?>
<?= $sf_response->addMeta('keywords', $keywords) ?>
<div class="contentLeft_02">
  <div class="profileBox">
    <h2>Информация о компании</h2>
    <div class="userpic">
      <?= image_tag_s($agency->logo) ?>
    </div>

    <table>
      <tr>
        <td colspan="2">
          <?= simple_format_text($agency->description) ?>
        </td>
      </tr>

      <tr>
        <th>Регион</th>
        <td>
          <?= $agency->Region ?>
        </td>
      </tr>

      <?php if ($agency->address): ?>
        <tr>
          <th>Адрес</th>
          <td>
            <?= $agency->address ?>
          </td>
        </tr>
      <?php endif ?>

      <?php if ($agency->phones): ?>
        <tr>
          <th>Телефон</th>
          <td>
            <?php foreach ($agency->phones as $phone): ?>
              <span class="tel"><?= $phone ?></span>
            <?php endforeach ?>
          </td>
        </tr>
      <?php endif ?>


      <?php if ($agency->url): ?>
        <tr>
          <th>Сайт</th>
          <td>
            <noindex><?= link_to($agency->url, $agency->url) ?></noindex>
          </td>
        </tr>
      <?php endif ?>
    </table>
  </div>

  <p>
    <?php if ('bti' == $agency->type): ?>
      <?= link_to('К списку БТИ региона', '@agencies_bti?region_id=' . $agency->region_id) ?>
    <?php else: ?>
      <?= link_to('К списку агентств региона', '@agencies?region_id=' . $agency->region_id) ?>
    <?php endif ?>
  </p>
  <?php include_partial('banner/block5-down-spec') ?>
</div>