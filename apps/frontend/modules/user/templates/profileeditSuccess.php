<div class="contentLeft_02">
  <div class="profileBox">
    <form action="<?= url_for('user/profile?edit=1') ?>" method="post" enctype="multipart/form-data" class="addForm">
      <table width="100%" cellpadding="0" cellspacing="0">
        <tbody>
          <tr>
            <td colspan="2">
              <h2>Основная информация</h2>
            </td>
          </tr>

          <?php if ($sf_user->type == 'company'): ?>
            <tr>
              <td>
                <?= label_for('user[company_name]', 'Название компании') ?>
              </td>
              <td class="field">
                <input type="text" name="user[company_name]" value="<?= $sf_user->company_name ?>" /><br />
              </td>
            </tr>
          <?php endif ?>

          <tr>
            <td>
              <?= label_for('user[name]', 'Имя') ?>
            </td>
            <td class="field">
              <input type="text" name="user[name]" value="<?= $sf_user->name ?>" /><br />
            </td>
          </tr>

          <tr>
            <td>
              <?= label_for('user[phone]', 'Телефон') ?>
            </td>
            <td class="field">
              <?php foreach($user_phone as $key=>$value): ?>
                <input type="text" name="user[phone][<?= $key ?>]" value="<?= $value ?>" class="input-<?= $key ?>" />
              <?php endforeach; ?>
            </td>
          </tr>

          <tr>
            <td>
              <?= $user_image_form['photo']->renderLabel($image_name) ?>
            </td>
            <td class="field">
              <?= $user_image_form['photo']->render() ?>
              <div style="font-size: 10px;">Минимальный размер 150x150</div>
              <?php if ($sf_user->photo): ?>
                <?= image_tag(photo($sf_user), 'class=logoProfile') ?><br />
              <?php endif ?>
            </td>
          </tr>

          <tr class="addButton">
            <td></td>
            <td>
              <span class="formButton button_01"><input type="submit" value="Сохранить изменения" /></span>
            </td>
          </tr>

          <tr>
            <td colspan="2">
              <h2>Дополнительные данные</h2>
            </td>
          </tr>

          <?php foreach($form as $field_name => $field): ?>
            <?php if (in_array($field_name, array('area', 'number'))) continue ?>
            <tr class="form-<?= $field_name ?>">
              <td>
                <?= $field->renderLabel() ?>
              </td>
              <td class="field">
                <?php if ($field_name == 'country'): ?>
                  <?php if ($form->getObject()->additional_phone): ?>
                    <?= $form['country']->render(array('class' => 'input-country')) ?>
                    <?= $form['area']->render(array('class' => 'input-area')) ?>
                    <?= $form['number']->render(array('class' => 'input-number')) ?>
                  <?php else: ?>
                    <?= $form['country']->render(array('class' => 'input-country',
                                                       'init'  => $form['country']->getValue(),
                                                       'value' => '')) ?>
                    <?= $form['area']->render(array(   'class' => 'input-area',
                                                       'init'  => $form['area']->getValue(),
                                                       'value' => '')) ?>
                    <?= $form['number']->render(array( 'class' => 'input-number',
                                                       'init'  => $form['number']->getValue(),
                                                       'value' => '')) ?>
                  <?php endif ?>
                <?php else: ?>
                  <?= $field ?>
                  <?php if ($field_name == 'site'): ?>
                    <?= $field->renderError() ?>
                  <?php endif ?>
                <?php endif ?>
              </td>
            </tr>
          <?php endforeach ?>

        </tbody>
        <tfoot>
          <tr class="addButton">
            <td></td>
            <td>
              <span class="formButton button_01"><input type="submit" value="Сохранить изменения" /></span>
            </td>
          </tr>
        </tfoot>
      </table>
    </form>
  </div>
</div>