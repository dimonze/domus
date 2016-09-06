<ul class="sf_admin_actions">  
<?php if ($form->isNew()): ?>
  <?php if ($sf_user->hasCredential(array(  0 => 'redactor-news-delete',))): ?>
<?php echo $helper->linkToDelete($form->getObject(), array(  'credentials' =>   array(    0 => 'redactor-news-delete',  ),  'params' =>   array(  ),  'confirm' => 'Are you sure?',  'class_suffix' => 'delete',  'label' => 'Delete',)) ?>
<?php endif; ?>

  <?php echo $helper->linkToList(array(  'params' =>   array(  ),  'class_suffix' => 'list',  'label' => 'Cancel',)) ?>
  <li class="sf_admin_action_save">
    <input type="submit" value="Сохранить" rel="post"/>
  </li>
  <?php echo $helper->linkToSaveAndAdd($form->getObject(), array(  'params' =>   array(  ),  'class_suffix' => 'save_and_add',  'label' => 'Save and add',)) ?>
<?php else: ?>
  <?php if ($sf_user->hasCredential(array(  0 => 'redactor-news-delete',))): ?>
<?php echo $helper->linkToDelete($form->getObject(), array(  'credentials' =>   array(    0 => 'redactor-news-delete',  ),  'params' =>   array(  ),  'confirm' => 'Are you sure?',  'class_suffix' => 'delete',  'label' => 'Delete',)) ?>
<?php endif; ?>

  <?php echo $helper->linkToList(array(  'params' =>   array(  ),  'class_suffix' => 'list',  'label' => 'Cancel',)) ?>
  <li class="sf_admin_action_save">
    <input type="submit" value="Сохранить" rel="post"/>
  </li>
  <?php echo $helper->linkToSaveAndAdd($form->getObject(), array(  'params' =>   array(  ),  'class_suffix' => 'save_and_add',  'label' => 'Save and add',)) ?>
<?php endif; ?>
</ul>