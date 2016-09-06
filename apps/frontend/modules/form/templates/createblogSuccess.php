<?php if ($form): ?>  
  <form action="/form/createblog" method="post" class="ajax-validate">
    <fieldset>
      <legend><span class="prependClose">Создание блога</span></legend>
      <?= $form['id']->render()?>
      <div><div>
        <label>Название блога:</label>
        <?= $form['title'] ?>
      </div></div>
      <div><div>
        <label>Предпочитаемый Url</label>
        <?= $form['url']?>
      </div></div>
      <div style="height: 30px;">
        <input class="popupSubmit send" type="submit" value="Отправить запрос" />
      </div>
    </fieldset>
  </form>
<?php endif ?>