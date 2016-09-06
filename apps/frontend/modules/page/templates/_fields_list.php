<table class="xml-fields-desc">
  <thead>
    <tr>
      <th width="200px">
        Название
      </th>
      <th width="150px">
        Xml-таг
      </th>
      <th>
        Возможные значения
      </th>
      <th width="200px">
        Описание
      </th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($fields as $f): ?>
          <tr>
            <td>
        <?= $f['label'] ?>
        </td>
        <td>
        <?= $f['xml_name'] ?>
        </td>
        <td>
        <?php switch($f['type']) {
           case 'float': ?>
            Дробное число
            <?php break;
           case 'integer': ?>
            Целое число
            <?php break;
           case 'year': ?>
            Год(только цифры), например "1983"
            <?php break;
          case 'select': ?>
            <ul>
              <?php foreach(explode("\n", $f['value']) as $val):?>
              <li>
                <?= $val ?>
              </li>
              <?php endforeach ?>
            </ul>
            <?php break;
          case 'price': ?>
            &lt;<?= $f['xml_name'] ?>&gt;<br />
            &nbsp;&nbsp;&lt;value&gt;123321&lt;/value&gt;<br />
            &nbsp;&nbsp;&lt;currency&gt;RUR&lt;/currency&gt;<br />
            &lt;/<?= $f['xml_name'] ?>&gt;<br />
            <?php break;
          case 'radio': ?>
            <ul>
              <?php foreach(explode("\n", $f['value']) as $val):?>
              <li>
                <?= $val ?>
              </li>
              <?php endforeach ?>
            </ul>
            <?php break;
          case 'radiocombo': ?>
            <ul>
              <?php foreach(explode("\n", $f['value']) as $val):?>
              <li>
                <?php
                if(strstr($val, 'select:')) {
                  $val = str_replace('select:', '', $val);
                  $val = explode('|', $val);
                  if(count($val) > 2) $val[1] = null;
                  $val = implode('', $val);
                }
                ?>
                <?= $val ?>
              </li>
              <?php endforeach ?>
            </ul>
            <?php break;
          case 'multiple': ?>
              <?= implode(', ', explode("\n", $f['value'])) ?>
            <?php break;
        } ?>
        </td>
        <td>
        <?= $f['xml_desc'] ?>
        </td>
      </tr>
    <?php endforeach ?>
  </tbody>
</table>