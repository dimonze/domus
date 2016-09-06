<div class="contentLeft_02">
  <?php include_component('pm','profilemessages') ?>
  <div class="favoritesObjects">
    <div class="pageHeader">
      <h1>Пакетная загрузка объявлений</h1>
    </div>
    <div class="pay-warn" style="border: 1px solid #ff9900; padding:10px; margin-bottom: 20px; font-size: 95%">
      <b>По всем вопросам связанным с работой пакетной загрузки обращайтесь <a href="mailto:import@mesto.ru">import@mesto.ru</a></b><br/>
    </div>
    <div class="pay-warn" style="border: 1px solid #ff9900; padding:10px; margin-bottom: 20px; font-size: 95%">
      Уважаемые пользователи, услуга платная, со стоимостью загрузки 
      в каждый раздел вы можете ознакомиться перейдя по кнопке "Узнать стоимость 
      и оплатить" или в разделе "Реклама". <br>До оплаты вы можете загрузить ваши 
      файлы для проверки соответствия формату xml. <br>Загружаться и показыватсья 
      на сайте объявления будут только после получения оплаты.<br>
      <span class="import_red"><strong>Об оплате счета обязательно сообщайте по электронной почте <a href="mailto:import@mesto.ru">import@mesto.ru</a></strong></span>
      <span class="formButton">
        <input type="button" value="Узнать стоимость и оплатить" onclick="location.href='/import/pay'">
      </span><br><br>
      <?php if($paid_types): ?>
      <b>Оплачена загрузка в разделы:</b><br/>
      <?php foreach($paid_types as $type): ?>
        <?=$type['name']?> (<?=$type['from']?> - <?=$type['to']?>)<br/>
      <?php endforeach;?>
      <?php else: ?>
        <b>Загрузка не оплачена ни в один раздел.</b><br/>
      <?php endif; ?>
    </div>
    <div class="ad-import">
      <?php include_partial('form', array('form' => empty($form) ? false : $form)) ?>
      <a class="goto-catalog" href="#import-help">Перейти к справочнику</a>
      <div class="clearBoth"></div>
      <?php include_partial('import_links', array('paid_types' => $paid_types)) ?>
      <?php if (!empty($pager)): ?>
        <?php include_partial('import_logs', array('pager' => $pager)) ?>
      <?php endif ?>
      <div id="import-help">
        <?php include_partial('apartament-sale-xml', array('formFields' => $formFields)) ?>
      </div>
    </div>
  </div>
</div>