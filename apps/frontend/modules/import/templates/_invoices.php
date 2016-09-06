<?php if(count($invoices) > 0):?>
  <div class="profileBox" style="max-height: 150px; overflow-y: scroll">
    <h2>Счета</h2>
    <table style="margin-left: 15px; width: 100%;">
    <?php foreach ($invoices as $invoice): ?>
      <tr>
        <td style="padding-right: 5px;">
          <a href="/import/invoice?id=<?=$invoice['id'] ?>" target="_blank">Счет №<?=$invoice['id']?></a> от <?=date('d.m.Y (H:i)', strtotime($invoice['created_at']))?>
        </td>
      </tr>
    <?php endforeach ?>
    </table>
  </div>
<?php endif ?>
