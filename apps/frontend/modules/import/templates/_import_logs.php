<?php if ($pager->getNbResults()):?>
  <div class="profileBox">
    <h2>Статистика пакетной загрузки объявлений</h2>
    <table class="import_logs">
      <tr>
        <th>Дата загрузки</th>
        <th>Название файла</th>
        <th>Тип файла</th>
        <th>Результат</th>
        <th>&nbsp;</th>
      </tr>

      <?php foreach ($pager->getResults() as $log): ?>
        <tr>
          <td><?= $log->created_at ?></td>
          <td><div class="import_file"><?= $log->file_name ?></div></td>
          <td><?= ImportFile::$types[$log->file_type] ?></td>
          <td class="import_log_counters">
            <span>
              Объявлений: <?= $log->lots ?>
            </span><br />
            <span class="import_green">
              Загружено: <?php $s=0; foreach ($log->Types as $t) $s += $t->lots; echo $s; ?>
            </span><br />
            <?php $errors = count($log->errors) && is_array($log->errors) ? count($log->errors) : $log->errors; ?>
            <?php if ($errors > 1): ?>
              <span class="import_red">
                Ошибок: <?= $errors ?> !
              </span><br />
            <?php endif ?>
            <?php if ($errors == 1): ?>
              <span class="import_red">
                <?= $log->Errors[0]->message ?>
              </span><br />
            <?php endif ?>
            <?php ?>
          </td>
          <td>
            <?=
              ($errors > 1) ?
                link_to('Ошибки!', 'import/exportErrorsToCsv?id=' . $log->id, 'class=import_red')
                . "<br><span style='font-size: 10px'>.CSV</span>" :
                '&nbsp;'
            ?>
          </td>
        </tr>
      <?php endforeach ?>
    </table>
    <?php
      include_partial(
        'global/pagination',
        array(
          'pager' => $pager,
          'styles' => 'width: 95%; margin-left: 10px;'
        )
      )
    ?>
  </div>
<?php endif ?>