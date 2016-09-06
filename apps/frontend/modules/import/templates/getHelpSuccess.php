<?php if (!empty($fields)): ?>
  <?php include_partial($type, array('formFields' => $formFields, 'fields' => $fields)) ?>
<?php else: ?>
  <?php include_partial($type, array('formFields' => $formFields)) ?>
<?php endif ?>