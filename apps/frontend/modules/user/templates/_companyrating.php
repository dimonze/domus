<div class="box companies">
  <h3><?= link_to('<span class="png24"></span>Рейтинг компаний', '@rating?type=company')?></h3>

  <?php if (count($companies)): ?>
    <ol>
      <?php foreach (array('first', 'second', 'third') as $i => $class): ?>
        <?php if (!empty($companies[$i])): ?>
          <li class="<?= $class ?>">
            <var></var>
            <?= image_tag(photo($companies[$i], 50, 50))?>
            <div>
              <?= link_to($companies[$i]->company_name, '@user_card?id=' . $companies[$i]->id)?>
              <span><?= $region ?></span>
            </div>
          </li>
        <?php endif ?>
      <?php endforeach ?>
    </ol>
  <?php endif ?>

  <?= link_to('Полный рейтинг компаний', '@rating?type=company', array('class' => 'bottom-link'))?>
</div>