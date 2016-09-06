<?php if (empty($pager) || !$pager->getNbResults()): ?>
  <div class="item">        
    <p>По запросу "<?= $query ?>" ничего не найдено.</p>
  </div>  
<?php elseif (!empty($pager) && count($pager->getNbResults()) > 0): ?>
  <?php foreach ($pager->getResults() as $id => $post): ?>            
    <div class="item">            
      <h6><?= format_h6_for_post($post)?></h6>
      <h4><?= link_to_post($post)?></h4>
      <p><?= $post->lid ?></p>
    </div>
  <?php endforeach ?>
<?php else: ?>
  <div class="item">        
    <p>По запросу "<?= $query ?>" ничего не найдено.</p>
  </div>  
<?php endif ?>
