<div class="box realtors">
  <h3><?= link_to('<span class="png24"></span>Рейтинг риэлторов', '@rating?type=realtor')?></h3>

  <?php if (count($realtors)): ?>
    <ol>
      <?php foreach (array('first', 'second', 'third') as $i => $class): ?>
        <?php if (!empty($realtors[$i])): ?>
          <li class="<?= $class ?>">
            <var></var>
            <?= image_tag(photo($realtors[$i], 50, 50))?>
            <div>
              <?= link_to($realtors[$i]->name, '@user_card?id=' . $realtors[$i]->id)?>
              <span><?= $region ?></span>
            </div>
          </li>
        <?php endif ?>
      <?php endforeach ?>
    </ol>
  <?php endif ?>

  <?= link_to('Полный рейтинг риэлторов', '@rating?type=realtor', array('class' => 'bottom-link'))?>
</div>