<h3>Инструкция по формированию файла XML для аренды домов и участков</h3>

<div class="ad-format">
  <a class="get-example top" href="/uploads/import/house-rent.xml" target="_blank">Скачать пример</a>
  <table>
    <thead>
      <tr>
        <th>Название</th>
        <th>Xml-тэг</th>
        <th>Возможные значения</th>
        <th>Обязательное поле</th>
        <th>Описание</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Открывающий тэг</td>
        <td>offer</td>
        <td>&lt;offer internal-id="1234567890"&gt;</td>
        <td>Да</td>
        <td>Открывающий тег с обязательным атрибутом internal-id</td>
      </tr>
      <tr>
        <td>Раздел недвижимости</td>
        <td>category</td>
        <td>
          <ul>
            <li>дом</li>
            <li>дома и участки</li>
          </ul>
        </td>
        <td>Да</td>
        <td>Раздел недвижимости</td>
      </tr>
      <tr>
        <td>Тип предложения</td>
        <td>type</td>
        <td>
          <ul>
            <li>аренда</li>
          </ul>
        </td>
        <td>Да</td>
        <td>Тип предложения</td>
      </tr>
      <tr>
        <td>Дата создания</td>
        <td>creation</td>
        <td><?= date('c')?></td>
        <td>Нет</td>
        <td>Дата создания. Если дата не указана, будет подставлена дата импорта объявления</td>
      </tr>
      <tr>
        <td>Дата истечения</td>
        <td>expire</td>
        <td><?= date('c')?></td>
        <td>Нет</td>
        <td>Дата истечения. Если дата не указана, она будет рассчитана автоматически в зависимости от типа объявления.</td>
      </tr>
      <tr>
        <td>Адрес</td>
        <td>location</td>
        <td>
          &lt;region&gt;
            Московская область
          &lt;/region&gt;<br />
          &lt;region_node&gt;
            Шатурский район
          &lt;/region_node&gt;<br />
          &lt;city&gt;
            г. Шатура
          &lt;/city&gt;<br />
          &lt;street&gt;ул. Тверская&lt;/street&gt;<br />
          &lt;address&gt;<br />
          &nbsp;&nbsp;&lt;house&gt;2&lt;/house&gt;<br />
          &nbsp;&nbsp;&lt;building&gt;1&lt;/building&gt;<br />
          &nbsp;&nbsp;&lt;structure&gt;3&lt;/structure&gt;<br />
          &lt;/address&gt;<br /><br />
          ИЛИ <br /><br />
          &lt;region&gt;
            Москва
          &lt;/region&gt;<br />
          &lt;metro&gt;
            Тверсткая
          &lt;/metro&gt;<br />
          &lt;street&gt;ул. Тверская&lt;/street&gt;<br />
          &lt;address&gt;<br />
          &nbsp;&nbsp;&lt;house&gt;2&lt;/house&gt;<br />
          &nbsp;&nbsp;&lt;building&gt;1&lt;/building&gt;<br />
          &nbsp;&nbsp;&lt;structure&gt;3&lt;/structure&gt;<br />
          &lt;/address&gt;
        </td>
        <td>Да, необходимо предоставить максимально полную информацию<br/>
          Тэг &lt;address&gt;&lt;/address&gt; - необязательный,
          при использовании соблюдать указаный формат</td>
        <td>Адрес</td>
      </tr>
      <tr>
        <td>Описание</td>
        <td>description</td>
        <td>Текст</td>
        <td>Нет</td>
        <td>Описание</td>
      </tr>
      <tr>
        <td>Широта</td>
        <td>latitude</td>
        <td>42.1266</td>
        <td>Нет</td>
        <td>Широта (Google)</td>
      </tr>
      <tr>
        <td>Долгота</td>
        <td>longitude</td>
        <td>42.1266</td>
        <td>Нет</td>
        <td>Долгота (Google)</td>
      </tr>
      <tr>
        <td>Информация об агенте</td>
        <td>agent</td>
        <td>
          &lt;agent&gt;<br />
          &nbsp;&nbsp;&lt;phone&gt;+7(495)979-91-40&lt;/phone&gt;<br />
          &nbsp;&nbsp;&lt;phone&gt;+7(495)979-91-41&lt;/phone&gt;<br />
          &nbsp;&nbsp;&lt;name&gt;Место.ру&lt;/name&gt;<br />
          &nbsp;&nbsp;&lt;link&gt;http://mesto.ru&lt;/link&gt;<br />
          &lt;/agent&gt;
        </td>
        <td></td>
        <td>
          Информация об агенте<br /><br />
          <b>Обратите внимание, каждый дополнительный номер телефона пишется в отдельном тэге phone</b>
        </td>
      </tr>
      <tr>
        <td>Фото объекта</td>
        <td>image</td>
        <td>
          &lt;image&gt;
            http://mesto.ru/pictures/ea1279882313.jpg
          &lt;/image&gt;<br />
          &lt;image&gt;
            http://mesto.ru/pictures/ea1279882313.jpg
          &lt;/image&gt;<br />
          &lt;image&gt;
            http://mesto.ru/pictures/ea1279882313.jpg
          &lt;/image&gt;<br />
        </td>
        <td></td>
        <td>
          Фото объекта. Должны быть указаны активные ссылки на фотографии,
          иначе мы не сможем их загрузить.
        </td>
      </tr>
      <?php foreach ($formFields as $field): ?>
        <?php include_partial('field', array('field' => $field))?>
      <?php endforeach ?>
    </tbody>
  </table>
  <a class="get-example bottom" href="/uploads/import/house-rent.xml" target="_blank">Скачать пример</a>
</div>