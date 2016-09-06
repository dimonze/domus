<div class="contentLeft_02">
  <?php include_component('pm','profilemessages') ?>
  <div class="favoritesObjects">
    <div class="pageHeader"><h1>Оплата пакетной загрузки</h1>
      Стоимость первоначального подключения услуги составляет 10 000 руб.
    </div>

    <?php if ($sf_user->type != 'company'): ?>
      <p>К сожалению на данный момент только пользователи типа "Компания" могут оплатить пакетную загрузку.</p>
    <?php else: ?>
      <?php include_partial('invoices', array('invoices' => $invoices)) ?>
      <div class="pageHeader">
        <h1>Заказать</h1>
      </div>
      <div class="ad-import">
        <form class="addform pay-import" action="<?= url_for('@import?action=pay') ?>" method="post">
          <?php foreach ($types as $element):?>
            <input type="checkbox" name="data[type][]" value="<?= $element['type']?>" <?= (!empty($last_params) && in_array($element['type'], $last_params)) ? 'checked' : ''?>>
            <?= $element['name']?>  <?= $element['price']?> руб.<br />
          <?php endforeach;?>
          <?php if(!empty($errors)): ?>
            <ul class="error_list">
              <li><?= $errors ?></li>
            </ul>
          <?php endif ?>
          <br>
          <?php if (!empty($data_form)): ?>
            <table cellpadding="3" cellspacing="3">
            <?php foreach ($data_form as $field_id => $field): ?>
              <?php if ($field_id == 'type'): ?>
                <?php continue; ?>
              <?php endif ?>
              <tr>
                <?php if ($field_id == 'i_agree'):?>
                  <td colspan="2">
                    <?= $field->render() ?> я согласен с <a href="/terms" target="_blank">договором офертой</a><br/>
                    <?= $field->renderError()?>
                  </td>
                <?php else: ?>
                  <td><?= $field->renderLabel() ?>*</td>
                  <td>
                    <?= $field->render() ?><br />
                    <?= $field->renderError()?>
                  </td>
                <?php endif ?>
              </tr>
            <?php endforeach ?>
            </table>
          <?php endif ?>
          <input type="submit" value="Заказать">
          <br/>
          <br/>
          * - обязательные поля<br><br>
          <span class="import_red"><strong>Об оплате счета обязательно сообщайте по электронной почте <a href="mailto:import@mesto.ru">import@mesto.ru</a></strong></span>
        </form>
        </div>
      <?php endif ?>
    </div>
  </div>
</div>