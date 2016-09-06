<?php if(isset($file)): ?>
  <div class="success">
    <span style="display: none;">smth wrong with IE</span>
    <?= input_tag('filename', $file, 'type=hidden') ?>
    <?= image_tag($thumbnail) ?>
  </div>
<?php else: ?>
  <div class="error">
    Can't upload file
  </div>
<?php endif ?>
