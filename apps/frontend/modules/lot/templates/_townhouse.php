<div class="townhouse cf">
  <?php echo $townhouse['id']; echo $townhouse['type']; ?>
  <table class="cottage-table">
    <tr>
      <td>
        <label for="<?= $townhouse['common_space']->renderId() ?>">Площадь дома, м<sup>2</sup></label>
      </td>
      <td>
        <?php echo $townhouse['common_space'] ?>
      </td>
    </tr>
    <tr>
      <td>
        <label for="<?= $townhouse['area_space']->renderId() ?>">Площадь участка, соток</label>
      </td>
      <td>
        <?php echo $townhouse['area_space'] ?>
      </td>
    </tr>
    <tr>
      <td>
        <label for="<?= $townhouse['price']->renderId() ?>">Цена</label>
      </td>
      <td>
        <div class="last">
          <?php echo $townhouse['price']->render(array('class' => 'townhouse_price')) ?>
          <?php echo $townhouse['currency'] ?>
        </div>
      </td>
    </tr>
    <tr>
      <td>
        <label for="<?= $townhouse['decription']->renderId() ?>">Описание</label>
      </td>
      <td>
        <?php echo $townhouse['decription'] ?>
      </td>
    </tr>
    <tr>
      <td></td>
      <td>
        <?php if ($add_button): ?>
          <div class="buttons">
            <span class="formButton"><input type="submit" value="Добавить таунхаус"/></span>
          </div>
          <?php else: ?>
          <div class="buttons">
            <span class="formButton formButtonDelete"><input type="button" value="Убрать таунхаус" onclick="return false;"/></span>
          </div>
        <?php endif; ?>
      </td>
    </tr>     
  </table>
</div>