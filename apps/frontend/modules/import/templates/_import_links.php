<?php if(count($sf_user->SourcesLinks) > 0):?>
  <div class="profileBox">
    <h2>Ссылки для автоматической загрузки объявлений</h2>
    <table style="margin-left: 15px; width: 100%;">
      <tr>
        <th style="width: 20px;">&nbsp;</th>
        <th style="width: 120px;">Формат источника объявлений</th>
        <th style="width: 50px;">Тип файла источника</th>
        <th style="width: 320px;">Ccылка</th>
        <th style="width: 40px;">Частота загрузки</th>
        <th>&nbsp;</th>
      </tr>
    <?php foreach ($sf_user->SourcesLinks as $link_id => $link): ?>
      <tr>
        <td style="padding-right: 5px;"><?= ++$link_id ?>
        </td>
        <td style="padding-right: 5px;"><?= $link->type_name ?></td>
        <td style="padding-right: 5px;"><?= $link->file_type_name ?></td>
        <td style="padding-right: 5px;">
          <a href="<?= $link->url ?>" target="_blank"><?= $link->url ?></a>
        </td>
        <td style="padding-right: 5px;"><?= UserSourcesLink::$frequencies[$link->frequency] ?></td>
        <td>
          <?php if($link->status == 'banned'): ?>
          <span class="import_red">Загрузка остановлена, ошибка формата&nbsp;XML</span><br/>
          <?=
            link_to(
              'Пере&shy;проверить',
              '@import?action=restoreSourceLink&id='
              . $link->id,
              'class=restore name=restore_source'
            )
          ?>
          <br/>
          <? endif; ?>
          <?php if($link->status == 'not-paid'): ?>
          <span class="import_red">Загрузка не оплачена</span><br/>
          <?=
            link_to(
              'Оплатить',
              '@import?action=pay',
              'class=restore name=restore_source'
            )
          ?>
          <br/>
          <?=
            link_to(
              'Пере&shy;проверить',
              '@import?action=restoreSourceLink&id='
              . $link->id,
              'class=restore name=restore_source'
            )
          ?>
          <br/>
          <? endif; ?>
          <?=
            link_to(
              'Удалить',
              '@import?action=deleteSourceLink&id='
              . $link->id,
              'class=delete name=delete_source'
            )
          ?>
        </td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td colspan="5"><div class="file-type-pay-info">
          <?php if(!empty($paid_types[$link->file_type])):?>
            Загрузка в раздел "<?=$paid_types[$link->file_type]['name']?>" <span class="paid-type">оплачена (<?=$paid_types[$link->file_type]['from']?> - <?=$paid_types[$link->file_type]['to']?>)</span>
          <?php else: ?>
            Загрузка в раздел "<?=preg_replace('/\s\(\w{3}\)\s?$/i', '', $link->file_type_name)?>" <span class="not-paid-type">не оплачена &nbsp;&nbsp;&nbsp;<a href="/import/pay">оплатить</a></span>
          <?php endif; ?> 
         </td>
      </tr>
    <?php endforeach ?>
    </table>
  </div>
<?php endif ?>
