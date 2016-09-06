<tr>
  <td class="colspan2" colspan="2">
    <div class="same-house">
      <?php if(isset($title)): ?><h3><?= $title ?></h3><?php endif; ?>
      <?php foreach ($form[$section.'s'] as $entity): ?>
      <?php include_partial($section, array($section => $entity, 'add_button' => true)) ?>
      <?php endforeach; ?>
    </div>
  </td>
</tr>