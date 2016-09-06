<?php if ($field->isPartial()): ?>
  <?php include_partial('user/'.$name, array('form' => $form, 'attributes' => $attributes instanceof sfOutputEscaper ? $attributes->getRawValue() : $attributes)) ?>
<?php elseif ($field->isComponent()): ?>
  <?php include_component('user', $name, array('form' => $form, 'attributes' => $attributes instanceof sfOutputEscaper ? $attributes->getRawValue() : $attributes)) ?>
<?php else: ?>
  <div class="<?php echo $class ?><?php $form[$name]->hasError() and print ' errors' ?>">
    <?= $form[$name]->renderError() ?>
    <div>
      <?= $form[$name]->renderLabel($label) ?>

      <div class="content user_image">
        <?php if ($name == 'Image' && $form->getObject()->photo): ?>
          <?= image_tag(photo($form->getObject(), 80, 80)) ?>
          <a name="user[delete_image]" href="#" rel="<?= $form['id']->getValue() ?>">Удалить</a>
        <?php endif ?>
        <?= $form[$name]->render($attributes instanceof sfOutputEscaper ? $attributes->getRawValue() : $attributes) ?></div>

      <?php if ($help): ?>
        <div class="help"><?php echo __($help, array(), 'messages') ?></div>
      <?php elseif ($help = $form[$name]->renderHelp()): ?>
        <div class="help"><?php echo $help ?></div>
      <?php endif ?>
    </div>
  </div>
<?php endif ?>