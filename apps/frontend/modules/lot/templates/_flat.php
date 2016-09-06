<div class="flat cf">
  <?php echo $flat['id'] ?>
  <div>
    <label for="">Кол-во комнат</label>
    <?php echo $flat['rooms'] ?>
  </div>

  <div>
    <label for="">Площадь, м<sup>2</sup></label>
    <span>
      <?php echo $flat['common_space'] ?><em>Общая</em>
    </span>
    <span>
      <?php echo $flat['living_space'] ?><em>Жилая</em>
    </span>
    <span>
      <?php echo $flat['kitchen_space'] ?><em>Кухня</em>
    </span>
  </div>

  <div>
    <label for="">Этаж</label>
    <?php echo $flat['floor'] ?>
  </div>

  <div>
    <h4>Балкон/лоджия</h4>
    <ul>
      <li><?php echo $flat['has_balcony'] ?><label for="">Балкон</label></li>
      <li><?php echo $flat['has_loggia'] ?><label for="">Лоджия</label></li>
    </ul>
  </div>

  <div class="last">
    <label>Стоимость квартиры</label>
    <?php echo $flat['price']->render(array('class' => 'flat_price')) ?>
    <?php echo $flat['currency'] ?>
  </div>

  <?php if ($add_button): ?>
  <div class="buttons">
    <span class="formButton"><input type="submit" value="Добавить квартиру"/></span>
  </div>
  <?php else: ?>
  <div class="buttons">
    <span class="formButton formButtonDelete"><input type="button" value="Убрать квартиру" onclick="return false;"/></span>
  </div>
  <?php endif; ?>
</div><!-- .flat -->
