<div class="profileBox">
  <h2>Настройки</h2>
  <form action="<?= url_for('user/save-settings') ?>" method="post">
    <table>
      <colgroup span="2">
        <col class="left-col" />
        <col class="right-col" />
      </colgroup>

      <tbody>
        <?php foreach ($sf_user->SettingsNamed as $settings): ?>
          <tr>
            <td><?= $settings->renderLabel() ?>:</td>
            <td><?= $settings->renderWidget() ?></td>
          </tr>
        <?php endforeach ?>
      </tbody>

      <tfoot>
        <tr class="addButton">
          <td></td>
          <td>
            <span class="formButton button_01"><input type="submit" value="Сохранить" /></span>
          </td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>