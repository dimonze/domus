<form action="<?= url_for('/form/consult') ?>" method="post" class="ajax-validate">
  <fieldset>
    <legend><span class="prependClose">Бесплатная консультация</span></legend>

    <div><div>
      <label>Предполагаемый объект:</label>
      <?= $form['type'] ?>
    </div></div>
    
    <div><div>
      <label>Географическое расположение:</label>
      <?= $form['where'] ?>
    </div></div>
    
    <div><div>
      <label>Предполагаемая сумма:</label>
      <?= $form['price'] ?>&nbsp;&nbsp;<span class="down-me">руб.</span>
    </div></div>
    
    <div><div>
      <label>Работаете ли Вы с риэлтором в данный момент?</label>
      <?= $form['hasrealtor'] ?>
    </div></div>
    
    <div><div>
      <label>Готовы ли Вы к сотрудничеству с риэлтором?</label>
      <?= $form['fearsrealtor'] ?>
    </div></div>
    
    <div><div>
      <label>Имя:</label>
      <?= $form['name'] ?>
    </div></div>
    
    <div><div>
      <label>Мобильный телефон:</label>
      <?= $form['phone'] ?>
    </div></div>
    
    <div><div>
      <label>E-mail:</label>
      <?= $form['email'] ?>
    </div></div>
    
    <div><div>
      <label>Комментарий:</label>
      <?= $form['comment'] ?>
    </div></div>

    <div style="height: 50px;">
      <input class="popupSubmit send" type="submit" value="Отправить" />
    </div>
  </fieldset>
</form>
