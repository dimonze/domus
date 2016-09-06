<?php if(count($lot->Cottages)): ?>
  <?php if (count($lot->getTypeCottages(Cottage::TYPE_COTTAGE))): ?>
    <h3>Коттеджи</h3>
    <div class="ad-format">
      <table>
        <thead>
          <tr>
            <th class="first">Площадь дома</th>
            <th>Площадь участка</th>
            <th>Цена</th>
            <th class="last">Описание</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($lot->getTypeCottages(Cottage::TYPE_COTTAGE) as $cottage): ?>
          <tr>
            <td class="first"><?= $cottage->common_space ?></td>
            <td><?= $cottage->area_space ?></td>
            <td><?= Currency::formatPrice($cottage->price, $cottage->currency, 'RUR');?></td>
            <td class="last"><?= $cottage->decription ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <br>
  <?php endif; ?>
  <?php if (count($lot->getTypeCottages(Cottage::TYPE_TOWNHOUSE))): ?>
    <h3>Таунхаусы</h3>
    <div class="ad-format">
      <table>
        <thead>
          <tr>
            <th class="first">Площадь дома</th>
            <th>Площадь участка</th>
            <th>Цена</th>
            <th class="last">Описание</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($lot->getTypeCottages(Cottage::TYPE_TOWNHOUSE) as $cottage): ?>
          <tr>
            <td class="first"><?= $cottage->common_space ?></td>
            <td><?= $cottage->area_space ?></td>
            <td><?= Currency::formatPrice($cottage->price, $cottage->currency, 'RUR');?></td>
            <td class="last"><?= $cottage->decription ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
<?php endif; ?>