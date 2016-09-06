<?php if (!empty($field)): ?>
  <tr>
    <td><?= $field->label ?></td>
    <?= (empty($no_xml)) ? '<td>' . $field->xml_name . '</td>' : '' ?>
    <td>
    <?php switch($field->type) {
       case 'float': ?>
        Дробное число, например "12.3456", разделитель - точка.
        <?php break;
       case 'integer': ?>
        Целое число
        <?php break;
       case 'year': ?>
        Год(только цифры), например "1983"
        <?php break;
      case 'select': ?>
        <ul>
          <?php foreach(explode("\n", $field['value']) as $val):?>
            <li><?= $val ?></li>
          <?php endforeach ?>
        </ul>
        <?php break;
      case 'price': ?>
        <?php if (!empty($no_xml)): ?>
          12345, RUR
        <?php else: ?>
          Целое число. Формат:<br />
          &lt;<?= $field->xml_name ?>&gt;<br />
          &nbsp;&nbsp;&lt;value&gt;123321&lt;/value&gt;<br />
          &nbsp;&nbsp;&lt;currency&gt;RUR/USD/EUR&lt;/currency&gt;<br />
          &lt;/<?= $field->xml_name ?>&gt;<br />
        <?php endif ?>
        <?php break;
      case 'radio': ?>
        <ul>
          <?php foreach(explode("\n", $field->value) as $val):?>
          <li><?= $val ?></li>
          <?php endforeach ?>
        </ul>
        <?php break;
      case 'radiocombo': ?>
        <ul>
          <?php foreach(explode("\n", $field->value) as $val):?>
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
          <?= implode(', ', explode("\n", $field->value)) ?>
        <?php break;
    } ?>
    </td>
    <td>
      <?php foreach ($field->FormField as $ff): ?>
        <?= ($ff->required === true) ? 'Да' : 'Нет' ?>
        <?php break ?>
      <?php endforeach ?>
    </td>
    <td><?= $field->xml_desc ?></td>
  </tr>
<?php endif ?>