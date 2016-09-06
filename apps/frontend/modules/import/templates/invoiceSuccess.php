<?php use_helper('Date') ?>
<div style="position: relative; padding-bottom: 75px; width: 800px;">
  <table cellspacing=0 cols=6 border=0 style="z-index: 2; position: relative; width: 800px">
  <colgroup>
    <col width=31><col width=325><col width=69><col width=62><col width=126><col width=126>
  </colgroup>
  <tbody>
    <tr>
      <td colspan="6" height=17 style="font-weight: bold; text-decoration: underline;">
        ООО &quot;АВ Медиа&quot;
      </td>
    </tr>
    <tr>
      <td colspan="6" height=17 style="font-weight: bold; text-decoration: underline;">
        ИНН: 7703647852/770401001
      </td>
    </tr>
    <tr>
      <td colspan="6" height=17 style="font-weight: bold;">
        Адрес: РФ, 119048, Москва, ул. Ефремова, д.14, офис А
      </td>
    </tr>
    <tr>
      <td colspan="6" height=17></td>
    </tr>
    <tr>
      <td colspan=6 height=21 align=center valign=middle style="font-weight: bold;">
        Образец заполнения платежного поручения
      </td>
    </tr>
    <tr>
      <td style="border-top: 1px solid #323232; border-left: 1px solid #323232" height=17>
        Получатель
      </td>
      <td style="border-top: 1px solid #323232"></td>
      <td style="border-top: 1px solid #323232"></td>
      <td style="border-top: 1px solid #323232; border-left: 1px solid #323232; border-right: 1px solid #323232"></td>
      <td style="border-top: 1px solid #323232; border-left: 1px solid #323232"></td>
      <td style="border-top: 1px solid #323232; border-right: 1px solid #323232"></td>
    </tr>
    <tr>
      <td style="border: 1px solid #323232; border-top: 0px;" colspan=2 height=34 Valign=TOP>
        ООО &quot;АВ Медиа&quot;
      </td>
      <td style="border-bottom: 1px solid #323232; border-left: 1px solid #323232"></td>
      <td style="border: 1px solid #323232; border-top: 0px;" align=center Valign=TOP>
        Сч. №
      </td>
      <td style="border: 1px solid #323232; border-top: 0px;" colspan=2 Valign=TOP>
        40702810662000010861
      </td>
    </tr>
    <tr>
      <td style="border-left: 1px solid #323232" height=17>
        Банк получателя
      </td>
      <td style="border-top: 1px solid #323232"></td>
      <td></td>
      <td style="border: 1px solid #323232" align=center Valign=middle>
        БИК
      </td>
      <td style="border-top: 1px solid #323232; border-left: 1px solid #323232" Valign=middle>
        044525352
      </td>
      <td style="border-top: 1px solid #323232; border-right: 1px solid #323232"></td>
    </tr>
    <tr>
      <td style="border: 1px solid #323232; border-top: 0px;" colspan=2 height=25 Valign=TOP>
        ОАО Банк &quot;ПЕТРОКОММЕРЦ&quot; г.Москва
      </td>
      <td style="border-bottom: 1px solid #323232; border-left: 1px solid #323232" Valign=TOP></td>
      <td style="border: 1px solid #323232; border-top: 0px;" align=center Valign=TOP>
        К/Сч. №
      </td>
      <td style="border-bottom: 1px solid #323232; border-left: 1px solid #323232" Valign=TOP>
        30101810700000000352
      </td>
      <td style="border-bottom: 1px solid #323232; border-right: 1px solid #323232"></td>
    </tr>
    <tr>
      <td colspan="6" height=17>&nbsp;</td>
    </tr>
    <tr>
      <td colspan=6 height=24 align=center style="font-weight: bold;">
        СЧЕТ № <?= $formatted_id ?> от <?= format_date($invoice['created_at'], 'D') ?>
      </td>
    </tr>
    <tr>
      <td colspan="6" height=24>&nbsp;</td>
    </tr>
    <tr>
      <td colspan="6" height=15>&nbsp;</td>
    </tr>
    <tr>
      <td colspan=6 height=20>
        Заказчик:
          <?= $invoice['company_name'] ?>
      </td>
    </tr>
    <tr>
      <td colspan=6 height=20>
        Плательщик:
<?= $invoice['company_name'] ?>
      </td>
    </tr>
    <tr>
      <td colspan="6" height=17></td>
    </tr>
    <tr>
      <td colspan="6" height=17></td>
    </tr>
    <tr>
      <td style="border: 1px solid #323232" height=68 align=center Valign=middle>
        №
      </td>
      <td style="border: 1px solid #323232; border-right: 0px;" align=center Valign=middle>
        Наименование товара
      </td>
      <td style="border: 1px solid #323232; border-right: 0px;" align=center>
        Единица<BR>измерения
      </td>
      <td style="border: 1px solid #323232; border-right: 0px;" align=center Valign=middle>
        Коли-<BR>чество
      </td>
      <td style="border: 1px solid #323232; border-right: 0px;" align=center Valign=middle>
        Цена
      </td>
      <td style="border: 1px solid #323232" align=center Valign=middle>
        Сумма
      </td>
    </tr>
    <?php $i=0; ?>
    <?php foreach ($invoice->Options as $option): ?>
      <tr>
        <td style="border: 1px solid #323232" height=34 align=right Valign=TOP>
          <?=++$i;?>
        </td>
        <td style="border-bottom: 1px solid #323232; border-left: 1px solid #323232" Valign=TOP>
          Размещение информации Заказчика на сайте www.mesto.ru в разделе "<?=$types[$option['type']]['name']?>"
        </td>
        <td style="border-bottom: 1px solid #323232; border-left: 1px solid #323232" align=center>
          шт
        </td>
        <td style="border-bottom: 1px solid #323232; border-left: 1px solid #323232" align=right>
          1
        </td>
        <td style="border-bottom: 1px solid #323232; border-left: 1px solid #323232" align=right>
          <?= number_format($types[$option['type']]['price'], 2, '.', '') ?>
        </td>
        <td style="border: 1px solid #323232; border-top: 0px;" align=right>
          <?= number_format($types[$option['type']]['price'], 2, '.', '') ?>
        </td>
      </tr>
    <?php endforeach;?>



    <tr>
      <td height=17></td>
      <td></td>
      <td></td>
      <td></td>
      <td align=right Valign=middle style="font-weight: bold;">Итого:</td>
      <td style="border: 1px solid #323232" align=right Valign=middle>
        <?= number_format($invoice->total, 2, '.', '') ?>
      </td>
    </tr>
    <tr>
      <td height=17></td>
      <td></td>
      <td></td>
      <td align=right></td>
      <td align=right>Всего к оплате:</td>
      <td style="border: 1px solid #323232" align=right Valign=middle>
        <?= number_format($invoice->total, 2, '.', '') ?>
      </td>
    </tr>
    <tr>
      <td colspan="6" height=17>&nbsp;</td>
    </tr>
    <tr>
      <td colspan="6" height=17>&nbsp;</td>
    </tr>
    <tr>
      <td colspan=6 height=26>Всего наименований <?=$i?>, на сумму <?= number_format($invoice->total, 2, '.', '') ?></td>
    </tr>
    <tr>
      <td colspan="6" height=40 Valign=TOP style="font-weight: bold;">
        <?= Toolkit::num2str($invoice->total) ?>. <span style="font-weight: normal">НДС не облагается.</span>
      </td>
    </tr>
    <tr>
      <td colspan="6" height=17>&nbsp;</td>
    </tr>
    <tr>
      <td colspan="6" height=33>Генеральный директор_____________________ (Волохович А.Ю.)</td>
    </tr>
  </tbody>
</table>
<span style="color: red; padding: 10px 0;"><strong>Об оплате счета обязательно сообщайте по электронной почте <a href="mailto:import@mesto.ru">import@mesto.ru</a></strong></span>
</div>