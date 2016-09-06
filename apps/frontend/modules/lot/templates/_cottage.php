<div class="cottage cf">
  <?php echo $cottage['id']; echo $cottage['type']; ?>
  <table class="cottage-table">
    <tr>
      <td>
        <label for="<?= $cottage['common_space']->renderId() ?>">Площадь дома, м<sup>2</sup></label>
      </td>
      <td>
        <?php echo $cottage['common_space'] ?>
      </td>
    </tr>
    <tr>
      <td>
        <label for="<?= $cottage['area_space']->renderId() ?>">Площадь участка, соток</label>
      </td>
      <td>
        <?php echo $cottage['area_space'] ?>
      </td>
    </tr>
    <tr>
      <td>
        <label for="<?= $cottage['price']->renderId() ?>">Цена</label>
      </td>
      <td>
        <div class="last">
          <?php echo $cottage['price']->render(array('class' => 'cottage_price')) ?>
          <?php echo $cottage['currency'] ?>
        </div>
      </td>
    </tr>
    <tr>
      <td>
        <label for="<?= $cottage['decription']->renderId() ?>">Описание</label>
      </td>
      <td>
        <?php echo $cottage['decription'] ?>
      </td>
    </tr>
    <tr>
      <td></td>
      <td>
        <?php if ($add_button): ?>
          <div class="buttons">
            <span class="formButton"><input type="submit" value="Добавить коттедж"/></span>
          </div>
          <?php else: ?>
          <div class="buttons">
            <span class="formButton formButtonDelete"><input type="button" value="Убрать коттедж" onclick="return false;"/></span>
          </div>
        <?php endif; ?>
      </td>
    </tr>    
  </table>
</div>