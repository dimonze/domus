<?php
  $news_portal_routes = array(
    'homepage', 'news_by_theme', 'news_by_section', 'author_article',
    'author_article_by_theme', 'expert_article', 'expert_article_by_theme',
    'posts', 'posts_by_theme', 'blogs', 'qa', 'agencies'
  );
?>
<?php // only for regions, no novostroyki & cottage ?>
<?php if(!sfConfig::get('is_new_building') && !sfConfig::get('is_cottage')): ?>
<div class="cityMenu">
  <a href="javacript://" toggle=".cityMenu .outlinemenu" id="citySelect" onclick="return false">
    <span><?= $sf_user->current_region->name ?></span>
  </a>
  <div class="outlinemenu" style="display: none">
  <iframe class="select-hider" frameborder="0"></iframe>
    <ul class="cityScroll">
      <?php foreach ($regions as $region): ?>
        <?php $url = Toolkit::getGeoHostByRegionId($region->id); ?>

        <?php if(sfConfig::get('is_new_building')): ?>
          <?php $url .= ''; ?>
        <?php elseif ($sf_context->getRouting()->getCurrentRouteName() == 'search' &&
                  $ctype = $sf_request->getParameter('current_type')): ?>
          <?php $url .= url_for('search', array('type' => $ctype)) ?>
        <?php elseif(in_array($sf_context->getRouting()->getCurrentRouteName(), $news_portal_routes)): ?>
          <?php $url .= url_for($sf_context->getRouting()->getCurrentInternalUri())?>
        <?php else: ?>
         <?php $url .= ''; ?>
        <?php endif ?>
        <?php if ($region->id != $sf_user->current_region->id): ?>  
          <?php if(!in_array($region->id, array(77,78,50,47,39,52))): ?> 
            <noindex><li rel="<?php echo $region->id ?>"><?= link_to($region->name, $url, array('rel' => 'nofollow')) ?></li></noindex>
          <?php else: ?>
            <li rel="<?php echo $region->id ?>"><?= link_to($region->name, $url) ?></li>
          <?php endif ?> 
        <?php else: ?>
             <?php if(!in_array($region->id, array(77,78,50,47,39,52))): ?> 
              <noindex>            
              <li rel="<?php echo $region->id ?>" class="current" style="display: none">
               <?= link_to($region->name, $url, array('rel' => 'nofollow')) ?>
              </li>
              </noindex>
            <?php else: ?>
              <li rel="<?php echo $region->id ?>" class="current" style="display: none">
               <?= link_to($region->name, $url) ?>
              </li>
            <?php endif ?> 
        <?php endif ?>
      <?php endforeach ?>
    </ul>

  </div>
</div>


<?php if(!empty($lots_in_region)): ?>
<!--   <div class="header-lots-count">
    <a href="#" class="top">
      <?= $lots_in_region ?>
    </a>
  </div> -->
<?php endif ?>

<?php endif ?>