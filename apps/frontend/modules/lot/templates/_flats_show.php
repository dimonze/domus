<?php if(count($lot->Flats)): ?>
<h3>Квартиры в этом доме</h3>
<div class="ad-format">
  <table>
    <thead>
      <tr>
        <th class="first">Комнаты</th>
        <th>Площадь</th>
        <th>Этаж</th>
        <th>Балкон/Лоджия</th>
        <th class="last">Стоимость</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($lot->Flats as $flat): ?>
      <tr>
        <th class="first"><?php echo $flat->rooms . ' ' . ending($flat->rooms, 'комната', 'комнаты', 'комнат') ?></th>
        <td>
          <ul>
            <li>Общая: <?php echo $flat->common_space ?></li>
            <li>Жилая: <?php echo $flat->living_space ?></li>
            <li>Кухня: <?php echo $flat->kitchen_space ?></li>
          </ul>
        </td>
        <td><?php echo $flat->floor ?></td>
        <td>
          <?php $bl = array() ?>
          <?php if ($flat->has_balcony) $bl[]= 'Балкон' ?>
          <?php if ($flat->has_loggia) $bl[]= 'Лоджия' ?>
          <?php echo implode(', ', $bl) ?>
        </td>
        <td class="last"><?php echo Currency::formatPrice($flat->price, $flat->currency, 'RUR');?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>