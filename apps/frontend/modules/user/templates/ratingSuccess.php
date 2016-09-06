<div class="contentLeft_02">
  <!--profileBox start -->

  <form class="favoritesForm" method="post" action="<?= url_for('@rating?type=' . $sf_params->get('type')) ?>">
    <div class="sortBox_02">
      <fieldset>
        <div class="floatLeft">
          <?= $form['region_id']->render(array('class' => 'select_06')) ?>
          <?= $form['speciality']->render(array('class' => 'select_06')) ?>
        </div>
        <div class="floatRight">
          <span class="formButton"><input type="submit" value="Найти" name="input"></span>
        </div>
        <div class="clearBoth"></div>
      </fieldset>
    </div>
    <div class="sortBox">
      <div class="floatLeft">
        <label>Сортировать по:</label>
        <?= $form['sort']->render(array(
          'class'    => 'select_05',
          'onchange' => '$(this).closest(\'form\').submit()',
        )) ?>
      </div>
      <?php if (!empty($pager)): ?>
        <div class="sortRight">
          <?php include_partial('global/pagination-small', array(
                                  'pager' => $pager,
                                  'route' => '@rating',
                                  'route_params' => array('type' => $sf_params->get('type'))
                                )) ?>
        </div>
      <?php endif ?>
      <div class="clearBoth"></div>
    </div>
  </form>


  <?php if (!empty($pager)): ?>
    <?php foreach ($pager->getResults() as $user): ?>
      <div class="realtor-rate-item">
        <div class="image">
          <?= image_tag(photo($user, 130, 150)) ?>
        </div>
        <div class="rate">
          Рейтинг <strong><?= (int) $user->rating ?></strong>
        </div>
        <div class="info">
          <?php if ($sf_params->get('type') == 'company'): ?>
            <h2><?= link_to($user->company_name, 'user_card', $user) ?><?= $user->approved ? '<div class="approved">Проверена</div>' : '' ?></h2>
          <?php else: ?>
            <h2><?= link_to($user->name, 'user_card', $user) ?></h2>
          <?php endif ?>

          <dl>
            <?php if ($user->company_name && $sf_params->get('type') != 'company'): ?>
              <dt>Компания:</dt>
              <dd><?= $user->company_name ?></dd>
            <?php endif ?>

            <?php if ($user->name && $sf_params->get('type') != 'realtor'): ?>
              <dt>Контактное лицо:</dt>
              <dd><?= $user->name ?></dd>
            <?php endif ?>

            <?php if ($user->Info->experience): ?>
              <dt>Опыт работы:</dt>
              <dd><?= $user->Info->experience ?></dd>
            <?php endif ?>
          </dl>
        </div>
        <ul>
          <li><?= $user->phone ?></li>
          <?php if ($user->Info->additional_phone): ?>
            <li><?= $user->Info->additional_phone ?></li>
          <?php endif ?>
        </ul>
      </div>
    <?php endforeach ?>

    <?php include_partial('global/pagination', array(
                            'pager' => $pager,
                            'route' => '@rating',
                            'route_params' => array('type' => $sf_params->get('type'))
                          )) ?>
  <?php endif ?>
</div>
<div class="contentRight_02" style="margin-top: -10px;">
  <?php include_partial('banner/block3-right-spec') ?>
</div>