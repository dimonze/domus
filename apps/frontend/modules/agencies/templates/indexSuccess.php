<?= $sf_response->addMeta('name', $h1_title) ?>
<?= $sf_response->addMeta('title', $h1_title) ?>
    
<div id="front">
  <div id="content">
    <div class="rc-box events header-bl-box without-bl-box">
      <?php foreach ($pager->getResults() as $agency): ?>
        <div class="item">
          <?php if ($logo = $agency->logo): ?>
            <?= link_to(
              image_tag_s($logo, 'style=float: left'),
              '@agency?id=' . $agency->id
            ) ?>
          <?php endif ?>
          <h3>
            <?= link_to($agency->name, '@agency?id=' . $agency->id) ?>
          </h3>
          <p>
            <?= $agency->description_short ?>
          </p>
        </div>
      <?php endforeach ?>
    </div>

    <?php include_partial('global/pagination', array('pager' => $pager)) ?>
    <?php include_partial('banner/block5-down-spec') ?>
  </div>
</div>
