<h3>Инструкция по формированию файла CSV для аренды коммерческой недвижимости</h3>

<div class="ad-format">
  <a class="get-example top" href="/uploads/import/commercial-rent.csv" target="_blank">Скачать пример</a>
  <table>
    <thead>
      <tr>
        <th>Название</th>        
        <th>Возможные значения</th>
        <th>Обязательное поле</th>
        <th>Описание</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Номер объявления</td>
        <td>целое число</td>
        <td>Да</td>
        <td>Номер объявления необходим для указания конкретного объявления, если возникла ошибка импорта.</td>
      </tr>
      <tr>
        <td>Тип недвижимости</td>
        <td>
          <ul>
            <li>коммерческая недвижимость</li>
          </ul>
        </td>
        <td>Да</td>
        <td>Тип недвижимости</td>
      </tr>
      <tr>
        <td>Тип предложения</td>        
        <td>
          <ul>
            <li>аренда</li>
          </ul>
        </td>
        <td>Да</td>
        <td>Тип предложения</td>
      </tr>
      <tr>
        <td>Регион</td>
        <td>г. Москва</td>
        <td>Да</td>
        <td>Укажите регион, в котором находится недвижимость</td>
      </tr>
      <tr>
        <td>Район/населённый пункт/метро</td>
        <td>Шатурский район, г. Шатура</td>
        <td>Да</td>
        <td>Район/населённый пункт/метро</td>
      </tr>
      <tr>
        <td>Адрес (улица, дом, корпус, строение)</td>
        <td>ул. Большевик, д.1, стр. 3</td>
        <td>Да</td>
        <td>Адрес (улица, дом, корпус, строение)</td>
      </tr>
      <tr>
        <td>Координаты объекта (ширина, долгота)</td>
        <td>34.123456, 55.123435</td>
        <td>Нет</td>
        <td>Координаты объекта (ширина, долгота)</td>
      </tr>
      <tr>
        <td>Дата создания</td>        
        <td><?= date('c')?></td>
        <td>Нет</td>
        <td>Дата создания. Если дата не указана, будет подставлена дата импорта объявления</td>
      </tr>
      <tr>
        <td>Дата истечения</td>        
        <td><?= date('c')?></td>
        <td>Нет</td>
        <td>Дата истечения. Если дата не указана, она будет рассчитана автоматически в зависимости от типа объявления.</td>
      </tr>            
      <tr>
        <td>Описание</td>        
        <td>Текст. Не более 1500 символов.</td>
        <td>Нет</td>
        <td>Описание</td>
      </tr>     
      <tr>
        <td>Дополнительная информация об агенте</td>
        <td>+7 (495) 979-91-40, Место.ру, http://mesto.ru/</td>
        <td>Нет</td>
        <td>Дополнительная информация об агенте</td>
      </tr>
      <tr>
        <td>Фото объекта</td>        
        <td>http://mesto.ru/pictures/ea1279882313.jpg ,<br />
            http://mesto.ru/pictures/ea1279882313.jpg          
        </td>
        <td>Нет</td>
        <td>
          Фото объекта. Должны быть указаны активные ссылки на фотографии,
          иначе мы не сможем их загрузить.
        </td>
      </tr>
      <?php foreach ($fields as $field): ?>
        <?php foreach ($formFields as $form_field): ?>
          <?php if ($field == $form_field->id): ?>
            <?php include_partial('field', array('field' => $form_field, 'no_xml' => true))?>
            <?php break ?>
          <?php endif ?>
        <?php endforeach?>
      <?php endforeach ?>
    </tbody>
  </table>
  <a class="get-example bottom" href="/uploads/import/commercial-rent.csv" target="_blank">Скачать пример</a>
</div>