<div class="profileBox">
  <h2>Сделать учётную запись неактивной</h2>
  <form action="<?= url_for('user/profile') ?>" method="post">
    <div class="padding_12">
      <label class="floatLeft">Введите пароль:
        <input name="delete" type="password" class="input_06"/>
      </label>
      <span class="formButton"><input name="input" type="submit" value="Сделать неактивной" /></span>
    </div>
  </form>
</div>